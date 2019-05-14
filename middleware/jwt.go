package midlleware

import (
	"net/http"

	"github.com/dgraph-io/badger"
	jwt "github.com/dgrijalva/jwt-go"
	"github.com/dgrijalva/jwt-go/request"
	"github.com/labstack/gommon/log"
	"github.com/pkg/errors"
)

var (
	// ErrNoAuthentication ошибка возвращается в случае
	// если авторизационные данные не были переданны
	ErrNoAuthentication = errors.New("No authentication")
	// ErrAccessDenied ошибка возникает если операция запрещена
	// для аккаунта, чьим секретным ключом был подписан запрос
	ErrAccessDenied = errors.New("Access denied")
	// ErrAccessTokenExpired ошибка возникает если в запросе использован
	// просроченный серектный ключ
	ErrAccessTokenExpired = errors.New("Access token is expired")
	// ErrUnknownAccessToken ошибка возникает, когда не секретный ключ
	// передан, но не его не удалось распознать.
	ErrUnknownAccessToken = errors.New("Unknown access token")

	errCodesMap = map[error]int{
		ErrNoAuthentication:   http.StatusUnauthorized,
		ErrAccessTokenExpired: http.StatusUnauthorized,
		ErrUnknownAccessToken: http.StatusUnauthorized,
		ErrAccessDenied:       http.StatusForbidden,
	}
)

// TODO: switch *badger.DB to internal wrapper
func ResolveToken(db *badger.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		// TODO: понять какая разница между этим методом и методом ParseFromRequestWithClaims
		// A: ParseFromRequest возвращает claims как map[string]interface{},
		//	  ParseFromRequestWithClaims сразу декодирует в нужный класс. Этим надо
		//    будет воспользоваться. (TODO)
		tok, err := request.ParseFromRequest(inreq, request.AuthorizationHeaderExtractor, todoKeyFunc)
		if err != nil {
			if err == request.ErrNoTokenInRequest {
				return errHandler(w, ErrNoAuthentication)
			}
			return errHandler(w, errors.Wrap(err, "parse request"))
		}

		if !tok.Valid {
			return errHandler(w, ErrAccessDenied)
		}

		// TODO: change request
		// TODO: clearHeaders
	}
}

// errHandler обрабатывает ошибки, которые происходят в процессе проксирования
// и возвращает их клиенту в том же формате, что API Target Mail
func errHandler(w http.ResponseWriter, err error) {
	if code, ok := errCodesMap[err]; ok {
		// Если в карте соотвествий ошибка - код найдена переданная ошибка
		// то запишем код в заголовок, а текст ошибки в тело, так будто ее вернул сам таргет.
		w.WriteHeader(code)
	} else {
		// В противном случае будем использвать 500
		// TODO: отловить текст таргета для 500ой ошибки
		w.WriteHeader(http.StatusInternalServerError)
	}

	if _, err := w.Write([]byte(err.Error())); err != nil {
		// FIXME
		log.Errorf("error handler write: %v", err)
	}
}

func todoKeyFunc(t *jwt.Token) (interface{}, error) {
	// TODO: по сути дела должна быть одна функция, которая возврщает ключ
	// в зависимости от клиента или чего-то в этом духе, а ключ можно хранить
	// в базе
	panic("not implemented")
}
