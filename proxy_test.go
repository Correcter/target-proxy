// Copyright 2011 The Go Authors. All rights reserved.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

// Reverse proxy tests.

package proxy

import (
	"bufio"
	"bytes"
	"io"
	"io/ioutil"
	"log"
	"net/http"
	"net/http/httptest"
	"net/url"
	"reflect"
	"strconv"
	"strings"
	"sync"
	"testing"
	"time"
)

func singleJoiningSlash(a, b string) string {
	aslash := strings.HasSuffix(a, "/")
	bslash := strings.HasPrefix(b, "/")
	switch {
	case aslash && bslash:
		return a + b[1:]
	case !aslash && !bslash:
		return a + "/" + b
	}
	return a + b
}

func NewSingleHostReverseProxy(target *url.URL) *ReverseProxy {
	targetQuery := target.RawQuery
	return NewReverseProxy(
		func(req *http.Request) {
			req.URL.Scheme = target.Scheme
			req.URL.Host = target.Host

			req.URL.Path = singleJoiningSlash(target.Path, req.URL.Path)

			if targetQuery == "" || req.URL.RawQuery == "" {
				req.URL.RawQuery = targetQuery + req.URL.RawQuery
			} else {
				req.URL.RawQuery = targetQuery + "&" + req.URL.RawQuery
			}
			if _, ok := req.Header["User-Agent"]; !ok {
				// explicitly disable User-Agent so it's not set to default value
				req.Header.Set("User-Agent", "")
			}
		},
	)
}

func TestReverseProxy(t *testing.T) {
	const backendResponse = "I am the backend"
	const backendStatus = 404
	backend := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == "GET" && r.FormValue("mode") == "hangup" {
			c, _, _ := w.(http.Hijacker).Hijack()
			c.Close()
			return
		}
		if len(r.TransferEncoding) > 0 {
			t.Errorf("backend got unexpected TransferEncoding: %v", r.TransferEncoding)
		}
		if r.Header.Get("X-Forwarded-For") == "" {
			t.Errorf("didn't get X-Forwarded-For header")
		}
		if c := r.Header.Get("Connection"); c != "" {
			t.Errorf("handler got Connection header value %q", c)
		}
		if c := r.Header.Get("Upgrade"); c != "" {
			t.Errorf("handler got Upgrade header value %q", c)
		}
		if c := r.Header.Get("Proxy-Connection"); c != "" {
			t.Errorf("handler got Proxy-Connection header value %q", c)
		}
		if g, e := r.Host, "some-name"; g != e {
			t.Errorf("backend got Host header %q, want %q", g, e)
		}
		w.Header().Set("Trailers", "not a special header field name")
		w.Header().Set("Trailer", "X-Trailer")
		w.Header().Set("X-Foo", "bar")
		w.Header().Set("Upgrade", "foo")
		w.Header().Add("X-Multi-Value", "foo")
		w.Header().Add("X-Multi-Value", "bar")
		http.SetCookie(w, &http.Cookie{Name: "flavor", Value: "chocolateChip"})
		w.WriteHeader(backendStatus)
		w.Write([]byte(backendResponse))
		w.Header().Set("X-Trailer", "trailer_value")
		w.Header().Set(http.TrailerPrefix+"X-Unannounced-Trailer", "unannounced_trailer_value")
	}))
	defer backend.Close()
	backendURL, err := url.Parse(backend.URL)
	if err != nil {
		t.Fatal(err)
	}
	proxyHandler := NewSingleHostReverseProxy(backendURL)
	proxyHandler.Log = log.New(ioutil.Discard, "", 0) // quiet for tests
	frontend := httptest.NewServer(proxyHandler)
	defer frontend.Close()
	frontendClient := frontend.Client()

	getReq, _ := http.NewRequest("GET", frontend.URL, nil)
	getReq.Host = "some-name"
	getReq.Header.Set("Connection", "close")
	getReq.Header.Set("Proxy-Connection", "should be deleted")
	getReq.Header.Set("Upgrade", "foo")
	getReq.Close = true
	res, err := frontendClient.Do(getReq)
	if err != nil {
		t.Fatalf("Get: %v", err)
	}
	if g, e := res.StatusCode, backendStatus; g != e {
		t.Errorf("got res.StatusCode %d; expected %d", g, e)
	}
	if g, e := res.Header.Get("X-Foo"), "bar"; g != e {
		t.Errorf("got X-Foo %q; expected %q", g, e)
	}
	if g, e := res.Header.Get("Trailers"), "not a special header field name"; g != e {
		t.Errorf("header Trailers = %q; want %q", g, e)
	}
	if g, e := len(res.Header["X-Multi-Value"]), 2; g != e {
		t.Errorf("got %d X-Multi-Value header values; expected %d", g, e)
	}
	if g, e := len(res.Header["Set-Cookie"]), 1; g != e {
		t.Fatalf("got %d SetCookies, want %d", g, e)
	}
	if g, e := res.Trailer, (http.Header{"X-Trailer": nil}); !reflect.DeepEqual(g, e) {
		t.Errorf("before reading body, Trailer = %#v; want %#v", g, e)
	}
	if cookie := res.Cookies()[0]; cookie.Name != "flavor" {
		t.Errorf("unexpected cookie %q", cookie.Name)
	}
	bodyBytes, _ := ioutil.ReadAll(res.Body)
	if g, e := string(bodyBytes), backendResponse; g != e {
		t.Errorf("got body %q; expected %q", g, e)
	}
	if g, e := res.Trailer.Get("X-Trailer"), "trailer_value"; g != e {
		t.Errorf("Trailer(X-Trailer) = %q ; want %q", g, e)
	}
	if g, e := res.Trailer.Get("X-Unannounced-Trailer"), "unannounced_trailer_value"; g != e {
		t.Errorf("Trailer(X-Unannounced-Trailer) = %q ; want %q", g, e)
	}

	// Test that a backend failing to be reached or one which doesn't return
	// a response results in a StatusBadGateway.
	getReq, _ = http.NewRequest("GET", frontend.URL+"/?mode=hangup", nil)
	getReq.Close = true
	res, err = frontendClient.Do(getReq)
	if err != nil {
		t.Fatal(err)
	}
	res.Body.Close()
	if res.StatusCode != http.StatusBadGateway {
		t.Errorf("request to bad proxy = %v; want 502 StatusBadGateway", res.Status)
	}

}

var proxyQueryTests = []struct {
	baseSuffix string // suffix to add to backend URL
	reqSuffix  string // suffix to add to frontend's request URL
	want       string // what backend should see for final request URL (without ?)
}{
	{"", "", ""},
	{"?sta=tic", "?us=er", "sta=tic&us=er"},
	{"", "?us=er", "us=er"},
	{"?sta=tic", "", "sta=tic"},
}

func TestReverseProxyQuery(t *testing.T) {
	backend := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("X-Got-Query", r.URL.RawQuery)
		w.Write([]byte("hi"))
	}))
	defer backend.Close()

	for i, tt := range proxyQueryTests {
		backendURL, err := url.Parse(backend.URL + tt.baseSuffix)
		if err != nil {
			t.Fatal(err)
		}
		frontend := httptest.NewServer(NewSingleHostReverseProxy(backendURL))
		req, _ := http.NewRequest("GET", frontend.URL+tt.reqSuffix, nil)
		req.Close = true
		res, err := frontend.Client().Do(req)
		if err != nil {
			t.Fatalf("%d. Get: %v", i, err)
		}
		if g, e := res.Header.Get("X-Got-Query"), tt.want; g != e {
			t.Errorf("%d. got query %q; expected %q", i, g, e)
		}
		res.Body.Close()
		frontend.Close()
	}
}

func TestReverseProxyCancelation(t *testing.T) {
	const backendResponse = "I am the backend"

	reqInFlight := make(chan struct{})
	backend := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		close(reqInFlight) // cause the client to cancel its request

		select {
		case <-time.After(10 * time.Second):
			// Note: this should only happen in broken implementations, and the
			// closenotify case should be instantaneous.
			t.Error("Handler never saw CloseNotify")
			return
		case <-w.(http.CloseNotifier).CloseNotify():
		}

		w.WriteHeader(http.StatusOK)
		w.Write([]byte(backendResponse))
	}))

	defer backend.Close()

	backend.Config.ErrorLog = log.New(ioutil.Discard, "", 0)

	backendURL, err := url.Parse(backend.URL)
	if err != nil {
		t.Fatal(err)
	}

	proxyHandler := NewSingleHostReverseProxy(backendURL)

	// Discards errors of the form:
	// http: proxy error: read tcp 127.0.0.1:44643: use of closed network connection
	proxyHandler.Log = log.New(ioutil.Discard, "", 0)

	frontend := httptest.NewServer(proxyHandler)
	defer frontend.Close()
	frontendClient := frontend.Client()

	getReq, _ := http.NewRequest("GET", frontend.URL, nil)
	go func() {
		<-reqInFlight
		frontendClient.Transport.(*http.Transport).CancelRequest(getReq)
	}()
	res, err := frontendClient.Do(getReq)
	if res != nil {
		t.Errorf("got response %v; want nil", res.Status)
	}
	if err == nil {
		// This should be an error like:
		// Get http://127.0.0.1:58079: read tcp 127.0.0.1:58079:
		//    use of closed network connection
		t.Error("Server.Client().Do() returned nil error; want non-nil error")
	}
}

func req(t *testing.T, v string) *http.Request {
	req, err := http.ReadRequest(bufio.NewReader(strings.NewReader(v)))
	if err != nil {
		t.Fatal(err)
	}
	return req
}

type bufferPool struct {
	get func() []byte
	put func([]byte)
}

func (bp bufferPool) Get() []byte  { return bp.get() }
func (bp bufferPool) Put(v []byte) { bp.put(v) }

func TestReverseProxyGetPutBuffer(t *testing.T) {
	const msg = "hi"
	backend := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		io.WriteString(w, msg)
	}))
	defer backend.Close()

	backendURL, err := url.Parse(backend.URL)
	if err != nil {
		t.Fatal(err)
	}

	var (
		mu  sync.Mutex
		log []string
	)
	addLog := func(event string) {
		mu.Lock()
		defer mu.Unlock()
		log = append(log, event)
	}
	rp := NewSingleHostReverseProxy(backendURL)
	const size = 1234
	rp.BufferPool = bufferPool{
		get: func() []byte {
			addLog("getBuf")
			return make([]byte, size)
		},
		put: func(p []byte) {
			addLog("putBuf-" + strconv.Itoa(len(p)))
		},
	}
	frontend := httptest.NewServer(rp)
	defer frontend.Close()

	req, _ := http.NewRequest("GET", frontend.URL, nil)
	req.Close = true
	res, err := frontend.Client().Do(req)
	if err != nil {
		t.Fatalf("Get: %v", err)
	}
	slurp, err := ioutil.ReadAll(res.Body)
	res.Body.Close()
	if err != nil {
		t.Fatalf("reading body: %v", err)
	}
	if string(slurp) != msg {
		t.Errorf("msg = %q; want %q", slurp, msg)
	}
	wantLog := []string{"getBuf", "putBuf-" + strconv.Itoa(size)}
	mu.Lock()
	defer mu.Unlock()
	if !reflect.DeepEqual(log, wantLog) {
		t.Errorf("Log events = %q; want %q", log, wantLog)
	}
}

func TestReverseProxy_Post(t *testing.T) {
	const backendResponse = "I am the backend"
	const backendStatus = 200
	var requestBody = bytes.Repeat([]byte("a"), 1<<20)
	backend := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		slurp, err := ioutil.ReadAll(r.Body)
		if err != nil {
			t.Errorf("Backend body read = %v", err)
		}
		if len(slurp) != len(requestBody) {
			t.Errorf("Backend read %d request body bytes; want %d", len(slurp), len(requestBody))
		}
		if !bytes.Equal(slurp, requestBody) {
			t.Error("Backend read wrong request body.") // 1MB; omitting details
		}
		w.Write([]byte(backendResponse))
	}))
	defer backend.Close()
	backendURL, err := url.Parse(backend.URL)
	if err != nil {
		t.Fatal(err)
	}
	proxyHandler := NewSingleHostReverseProxy(backendURL)
	frontend := httptest.NewServer(proxyHandler)
	defer frontend.Close()

	postReq, _ := http.NewRequest("POST", frontend.URL, bytes.NewReader(requestBody))
	res, err := frontend.Client().Do(postReq)
	if err != nil {
		t.Fatalf("Do: %v", err)
	}
	if g, e := res.StatusCode, backendStatus; g != e {
		t.Errorf("got res.StatusCode %d; expected %d", g, e)
	}
	bodyBytes, _ := ioutil.ReadAll(res.Body)
	if g, e := string(bodyBytes), backendResponse; g != e {
		t.Errorf("got body %q; expected %q", g, e)
	}
}
