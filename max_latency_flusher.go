package proxy

import (
	"io"
	"net/http"
	"sync"
	"time"
)

type writeFlusher interface {
	io.Writer
	http.Flusher
}

type maxLatencyWriter struct {
	sync.Mutex

	flusher writeFlusher
	latency time.Duration

	done chan struct{}
}

func (m *maxLatencyWriter) Write(p []byte) (int, error) {
	m.Lock()
	defer m.Unlock()

	return m.flusher.Write(p)
}

func (m *maxLatencyWriter) flushLoop() {
	ticker := time.NewTicker(m.latency)
	defer ticker.Stop()

	for {
		select {
		case <-m.done:
			return
		case <-ticker.C:
			m.Lock()
			m.flusher.Flush()
			m.Unlock()
		}
	}

	panic("max latency flusher: flush loop: unreachable")
}

func (m *maxLatencyWriter) stop() { m.done <- struct{}{} }
