package tokenizer

import (
	"github.com/pkg/errors"

	ldap "gopkg.in/ldap.v2"
)

// ErrInvalidCredentials is error that returns by LdapChecker.Valid method
// in case of invalid user credentials
var ErrInvalidCredentials = errors.New("invalid credentials")

// QueryFunc is function that returns ldap filter string
type QueryFunc = func(user string) string

// LdapChecker is a simple struct that contains methods for user validation throught ldap
type LdapChecker struct {
	conn *ldap.Conn
	user string
	pass string
	// BaseDN base search dn
	BaseDN string
	// Query is user search query
	Query QueryFunc
}

// NewLdapChecker is an Ldap checker consturctor
func NewLdapChecker(addr, user, pass, basedn string, query QueryFunc) (*LdapChecker, error) {
	if query == nil {
		return nil, errors.New("query must be no nil")
	}

	conn, err := ldap.Dial("tcp", addr)
	if err != nil {
		return nil, errors.Wrap(err, "new ldap")
	}

	l := &LdapChecker{
		conn:   conn,
		user:   user,
		pass:   pass,
		BaseDN: basedn,
		Query:  query,
	}

	if err = l.rebind(); err != nil {
		conn.Close()

		return nil, errors.Wrap(err, "new ldap")
	}

	return l, nil
}

func (l *LdapChecker) rebind() error {
	return errors.Wrap(l.bind(l.user, l.pass), "rebind")
}

func (l *LdapChecker) bind(user, pass string) error {
	return errors.Wrap(l.conn.Bind(user, pass), "bind")
}

// Close closes underlying LDAP connection
func (l *LdapChecker) Close() {
	l.conn.Close()
}

// Valid checks if LDAP user credentials is valid
func (l *LdapChecker) Valid(user, pass string) error {
	if len(user)+len(pass) < 2 {
		return ErrInvalidCredentials
	}

	sr, err := l.conn.Search(
		ldap.NewSearchRequest(
			l.BaseDN,
			ldap.ScopeWholeSubtree,
			ldap.NeverDerefAliases,
			0,              // SizeLimit
			0,              // TimeLimit
			false,          // TypesOnly
			l.Query(user),  // Search query
			[]string{"dn"}, // attributes
			nil,            // Control do not know what it is
		),
	)
	if err != nil {
		return errors.Wrap(err, "valid")
	}

	if len(sr.Entries) == 0 {
		// nothing found means that credentials is invalid
		return ErrInvalidCredentials
	}

	defer l.rebind() // switch back to read-only user

	for _, entry := range sr.Entries {
		// lets try all of it
		if err = l.bind(entry.DN, pass); err != nil {
			if ldapErr, ok := err.(*ldap.Error); ok {
				if ldapErr.ResultCode == ldap.LDAPResultInvalidCredentials {
					// if credentials is invlaid try another DM
					continue
				}
				return errors.Wrap(err, "valid")
			}
		} else {
			// if no errors than user is valid
			return nil
		}
	}

	return ErrInvalidCredentials
}
