package proxy

import "net/http"

// proxyResponseWriter имплемeнтирует http.ResponseWriter, но позвляет
// отслеживать запись в него.
type proxyResponseWriter struct {
	// writer is a source http.ResponseWriter
	writer http.ResponseWriter
	used   bool
}

// newProxyResponseWriter is constructor for proxyResponseWriter
func newProxyResponseWriter(w http.ResponseWriter) *proxyResponseWriter {
	return &proxyResponseWriter{writer: w}
}

// Header implements http.ResponseWriter interface
func (p *proxyResponseWriter) Header() http.Header {
	p.used = true
	return p.writer.Header()
}

// Writer implements http.ResponseWriter interface
func (p *proxyResponseWriter) Write(b []byte) (int, error) {
	p.used = true
	return p.writer.Write(b)
}

// WriteHeader implements http.ResponseWriter interface
func (p *proxyResponseWriter) WriteHeader(code int) {
	p.used = true
	p.writer.WriteHeader(code)
}
