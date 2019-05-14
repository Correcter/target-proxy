package proxy

import (
	"context"
	"io"
	"log"
	"net"
	"net/http"
	"os"
	"strings"
	"time"
)

var stdLogger = log.New(os.Stderr, "proxy: ", log.Lshortfile)

// ReverseProxy на деле это урезанная версия httputil.ReverseProxy, которая поддерживает
// авторизацияю через jwt token
type ReverseProxy struct {
	middleware []http.HandlerFunc

	// director - это функция, которая модифицрует приходящий запрос
	// таким образом чтобы направить его на нужный сервер.
	director func(*http.Request)

	// ModifiyResponse - это опциональный параметр, который позволяет модифицировать
	// ответ сервера перед отправкой обратно, клиенту.
	// NOTE: если функция ModifiyResponse прочитатет тело запроса и не уставновит на его
	// место новый io.ReadCloser тело не будет переданно клиенту
	// TODO
	ModifiyResponse func(http.Response) error

	// Transport - структура, которая отсылает запрос на удаленный сервер,
	// в случае если Transport не указан будет использован http.DefaultTransport
	Transport http.RoundTripper

	// Log - логгер прокси, если Log не указан будет использован logrus.Log
	Log Logger

	BufferPool    BufferPool
	FlushInterval time.Duration
}

// Logger - это интерфейс необхоимы для логгирования ReverseProxy
type Logger interface {
	Printf(format string, v ...interface{})
	Println(v ...interface{})
}

// BufferPool - позволяет переиспользовать алоцированые буфферы.
type BufferPool interface {
	Get() []byte
	Put([]byte)
}

// NewReverseProxy is a ReverseProxy constructor
func NewReverseProxy(director func(*http.Request)) *ReverseProxy {
	return &ReverseProxy{director: director}
}

// Use adds single middleware to ReverseProxy
func (p *ReverseProxy) Use(h http.HandlerFunc) *ReverseProxy {
	if h != nil {
		p.middleware = append(p.middleware, h)
	}

	return p
}

// ServeHTTP impelements http.Handler interface
// 1. Вытщать JWT token из запроса, если токена нет то сразу отдать ошибку
//	  желательно в том же формате что и сам таргет. JWT токен должен содержаться
//    в заголовоке Authorization запроса с префиксом "Bearer ".
func (p *ReverseProxy) ServeHTTP(w http.ResponseWriter, inreq *http.Request) {
	if p.director == nil {
		panic("reverse proxy director is nil")
	}

	if len(p.middleware) > 0 {
		// Создадим прокси для http.ResponseWriter, чтобы отслеживать запись в него
		pw := newProxyResponseWriter(w)

		for _, middleware := range p.middleware {
			if middleware == nil {
				continue
			}

			middleware(pw, inreq)
			// если промежуточный обработчик использовал ResponseWriter для
			// записи значит возращаем ответ буз последующей обработки.
			if pw.used {
				return
			}
		}
	}

	ctx := inreq.Context()
	if notifier, ok := w.(http.CloseNotifier); ok {
		// Если ResponseWriter имплементирует http.CloseNotifier интерфейс
		// то мы можем отслеживать, когда клиент закрыл соединение и
		// прервать все обработки используя функцию cancel
		var cancel context.CancelFunc

		ctx, cancel = context.WithCancel(ctx)
		defer cancel()

		nc := notifier.CloseNotify()

		go func() {
			select {
			case <-nc:
				cancel()
			case <-ctx.Done():
			}
		}()
	}

	// создадим исходящий запрос с контекстом
	outreq := inreq.WithContext(ctx)

	if inreq.ContentLength == 0 {
		outreq.Body = nil // https://github.com/golang/go/issues/16036
	}

	outreq.Header = cloneHeader(inreq.Header)

	p.director(outreq)

	outreq.Close = false

	// чистим заголовки запроса
	clearHeader(outreq.Header, removeHeaders)
	if clientIP, _, err := net.SplitHostPort(inreq.RemoteAddr); err == nil {
		// If we aren't the first proxy retain prior
		// X-Forwarded-For information as a comma+space
		// separated list and fold multiple headers into one.
		if prior, ok := outreq.Header["X-Forwarded-For"]; ok {
			clientIP = strings.Join(prior, ", ") + ", " + clientIP
		}
		outreq.Header.Set("X-Forwarded-For", clientIP)
	}

	res, err := p.transport().RoundTrip(outreq)
	if err != nil {
		p.log().Printf("proxy error: %v", err)

		w.WriteHeader(http.StatusBadGateway)
		return
	}

	// чистим и копируем заголовки ответа
	clearHeader(res.Header, removeHeaders)
	copyHeader(w.Header(), res.Header)

	ntrailer := len(res.Trailer)
	if ntrailer > 0 {
		trailerKeys := make([]string, 0, ntrailer)
		for k := range res.Trailer {
			trailerKeys = append(trailerKeys, k)
		}
		w.Header().Add("Trailer", strings.Join(trailerKeys, ", "))
	}

	w.WriteHeader(res.StatusCode)
	if len(res.Trailer) > 0 {
		// Force chunking if we saw a response trailer.
		// This prevents net/http from calculating the length for short
		// bodies and adding a Content-Length.
		if fl, ok := w.(http.Flusher); ok {
			fl.Flush()
		}
	}

	p.copyResponse(w, res.Body)
	res.Body.Close() // close now, instead of defer, to populate res.Trailer

	if len(res.Trailer) == ntrailer {
		copyHeader(w.Header(), res.Trailer)
		return
	}

	for k, vv := range res.Trailer {
		k = http.TrailerPrefix + k
		for _, v := range vv {
			w.Header().Add(k, v)
		}
	}

}

// transport Возвращает транспорт из ReverseProxy или http.DefaultTransport
// если ReverseProxy.Transport == nil
func (p *ReverseProxy) transport() http.RoundTripper {
	if p.Transport != nil {
		return p.Transport
	}
	return http.DefaultTransport
}

func (p *ReverseProxy) log() Logger {
	if p.Log == nil {
		return stdLogger
	}
	return p.Log
}

func (p *ReverseProxy) copyResponse(dst io.Writer, src io.Reader) {
	if p.FlushInterval != 0 {
		if flusher, ok := dst.(writeFlusher); ok {
			mlw := &maxLatencyWriter{
				flusher: flusher,
				latency: p.FlushInterval,
				done:    make(chan struct{}),
			}
			go mlw.flushLoop()
			defer mlw.stop()

			dst = mlw
		}
	}

	buf := p.getBuffer()
	if _, err := io.CopyBuffer(dst, src, buf); err != nil && err != context.Canceled {
		p.log().Printf("reverse proxy: error during copy %v", err)
	}
	p.putBuffer(buf)
}

func (p *ReverseProxy) getBuffer() []byte {
	if p.BufferPool == nil {
		// TODO: make constant or ReverseProxy parameter for size
		return make([]byte, 2<<11)
	}
	return p.BufferPool.Get()
}

func (p *ReverseProxy) putBuffer(b []byte) {
	if p.BufferPool == nil {
		return
	}

	p.BufferPool.Put(b)
}

// removeHeaders - это массив заголовков, которые необходимо удалять
var removeHeaders = []string{
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html hop-by-hop заголовоки
	// не совсем уверен о чем они, почитать (TODO)
	"Connection", "Proxy-Connection", "Keep-Alive", "Proxy-Authenticate",
	"Proxy-Authorization", "Te", "Trailer", "Transfer-Encoding", "Upgrade",
}

func clearHeader(h http.Header, remove []string) {
	// See RFC 2616, section 14.10.
	if c := h.Get("Connection"); c != "" {
		for _, f := range strings.Split(c, ",") {
			if f = strings.TrimSpace(f); f != "" {
				h.Del(f)
			}
		}
	}

	// удаляем все лишние заголовки
	for _, v := range remove {
		if h.Get(v) != "" {
			h.Del(v)
		}
	}
}

func cloneHeader(src http.Header) http.Header {
	dst := make(http.Header, len(src))

	for key, oval := range src {
		nval := make([]string, len(oval))
		copy(nval, oval)

		dst[key] = nval
	}

	return dst
}

func copyHeader(dst, src http.Header) {
	for key, values := range src {
		for _, val := range values {
			dst.Add(key, val)
		}
	}
}
