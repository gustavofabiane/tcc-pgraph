<?php

namespace Pgraph\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * Allowes URI schemes
     *
     * @var array
     */
    const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * The URI scheme
     *
     * @var string
     */
    protected $scheme;

    /**
     * The URI authority
     *
     * @var string
     */
    protected $authority;

    /**
     * The URI user name
     *
     * Present in user information
     *
     * @var string
     */
    protected $username;

    /**
     * The URI user password
     *
     * Present in user information
     *
     * @var string
     */
    protected $password;

    /**
     * The URI user information
     *
     * Format: username[:password]
     *
     * @var string
     */
    protected $userInfo;

    /**
     * The URI host
     *
     * @var string
     */
    protected $host;

    /**
     * The URI port
     *
     * @var int
     */
    protected $port;

    /**
     * The URI path
     *
     * @var string
     */
    protected $path;

    /**
     * The URI query
     *
     * @var string
     */
    protected $query;

    /**
     * The URI fragment
     *
     * @var string
     */
    protected $fragment;

    /**
     * Creates a new URI.
     *
     * @param string $scheme The URI Scheme
     * @param string $host The URI host
     * @param string $port The URI port
     * @param string $path The URI path
     * @param string $query The URI query string
     * @param string $fragment The URI fragment
     * @param string $username The URI username for user information
     * @param string $password The URI user password for user information
     */
    public function __construct(
        string $scheme,
        string $host,
        int $port = null,
        string $path = null,
        string $query = null,
        string $fragment = null,
        string $username = null,
        string $password = null
    ) {
        if (!$this->isValidScheme($scheme)) {
            throw new \InvalidArgumentException('The given scheme (\'' . $scheme . '\') is not valid');
        }
        if (!$this->isValidPort($port)) {
            throw new \InvalidArgumentException('The port ' . $port . ' is not valid');
        }
        $this->scheme = $scheme ?: null;
        $this->host = $host ?: null;
        $this->port = $port ?: null;
        $this->path = $this->normalizePath($path) ?: null;
        $this->query = $this->normalizeQuery(ltrim($query, '?')) ?: null;
        $this->fragment = $this->normalizeQuery(ltrim($fragment, '#')) ?: null;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Creates a new URI object from a URL parsed with parse_url()
     *
     * Note: This method is not part of the PSR-7 specification.
     *
     * @param array $url An array with URI data provided from parse_url()
     * @return static
     */
    public static function createFromParsedUrl(array $url)
    {
        $scheme = $url['scheme'] ?? '';
        $host = $url['host'] ?? '';
        $port = isset($url['port']) ? (int) $url['port'] : null;
        $path = $url['path'] ?? '';
        $query = $url['query'] ?? '';
        $fragment = $url['fragment'] ?? '';
        $username = $url['user'] ?? '';
        $password = $url['pass'] ?? '';

        return new static(
            $scheme, $host, $port, $path,
            $query, $fragment, $username, $password
        );
    }

    /**
     * Creates a new URI object from the server params array.
     * 
     * Note: This method is not part of the PSR-7 specification.
     *
     * @param array $serverParams
     * @return static
     */
    public static function createFromServerParams(array $serverParams)
    {
        // change server params keys to lower case
        $serverParams = array_change_key_case($serverParams, CASE_LOWER);

        // scheme
        $scheme = isset($serverParams['https']) && $serverParams['https'] === 'On' ? 'https' : 'http';
        
        // host
        $host = $serverParams['http_host'] ?? $serverParams['server_name'];

        // port
        $port = (int) ($serverParams['server_port'] ?? 80);
        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];
            if (isset($matches[2])) {
                $port = (int) substr($matches[2], 1);
            }
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $port = (int) substr($host, $pos + 1);
                $host = strstr($host, ':', true);
            }
        }

        //path
        $path = parse_url('http://default.com' . $serverParams['request_uri'], PHP_URL_PATH);

        // query string
        $query = $serverParams['query_string'] ?? '';
        if (!$query) {
            $query = parse_url('http://default.com' . $serverParams['request_uri'], PHP_URL_QUERY);
        }

        // fragment
        $fragment = parse_url('http://default.com' . $serverParams['request_uri'], PHP_URL_FRAGMENT);

        // user information
        $username = $serverParams['php_auth_user'] ?? '';
        $password = $serverParams['php_auth_pw'] ?? '';
        
        return new static(
            $scheme, $host, $port, $path,
            $query, $fragment, $username, $password
        );
    }

    /**
     * Create an URI object from a string.
     *
     * Note: This method is not part of the PSR-7 specification.
     *
     * @param string $uri A valid URI string
     *               ex. http://user:pass@host:80/path?query#fragment
     * @return static
     */
    public static function createFromString(string $uri)
    {
        return static::createFromParsedUrl(parse_url($uri));
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme ?: '';
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        if (!$this->authority) {
            $authority = '';
            $userInfo = $this->getUserInfo();
            if ($userInfo) {
                $authority .= ($userInfo . '@');
            }
            $authority .= $this->host;
            if ($this->port) {
                $authority .= (':' . $this->port);
            }
            $this->authority = $authority;
        }
        return $this->authority ?: '';
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        if (!$this->userInfo) {
            $info = $this->username;
            if ($this->password) {
                $info .= (':' . $this->password);
            }
            $this->userInfo = $info;
        }
        return $this->userInfo ?: '';
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host ?: '';
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        if (!$this->port && $this->scheme) {
            $port = ($this->scheme === 'http' && $this->port === 80) ||
                    ($this->scheme === 'https' && $this->port === 443);
        } elseif (!$this->port && !$this->scheme) {
            return null;
        }
        return $this->port;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path ?: '';
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query ?: '';
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        if ($this->fragment && strpos($this->fragment, '#') === 0) {
            $this->fragment = substr($this->fragment, 1);
        }
        return $this->fragment ?: '';
    }
 
    /**
     * Checks if the given string is a valid scheme.
     *
     * The scheme will be normalized to lowercase.
     *
     * Note: This method is not part of the PSR-7 specification.
     *
     * @param string $scheme The scheme to be checked.
     * @return bool
     */
    public function isValidScheme($scheme)
    {
        return is_null($scheme) || empty($scheme) || (is_string($scheme) && in_array(strtolower($scheme), static::ALLOWED_SCHEMES));
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        if (!$this->isValidScheme($scheme)) {
            throw new \InvalidArgumentException('The given scheme (\'' . $scheme . '\') is not valid');
        }
        $clone = clone $this;
        $clone->scheme = $scheme ? strtolower($scheme) : null;

        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        if ($user) {
            $clone->username = $user;
            $clone->password = $password;
        } else {
            $clone->username = null;
            $clone->password = null;
        }
        $clone->userInfo = null;

        return $clone;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     * @return static A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $host ? strtolower($host) : null;

        return $clone;
    }

    /**
     * Checks if the given port is valid
     *
     * @param int $port
     * @return bool
     */
    public function isValidPort($port)
    {
        return is_null($port) || (is_int($port) && ($port >= 1 && $port <= 65535));
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *     removes the port information.
     * @return static A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        if (!$this->isValidPort($port)) {
            throw new \InvalidArgumentException('The port ' . $port . ' is not valid');
        }
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * Normalizes the given path string.
     *
     * This method is used from Slim Framework URI implementation
     * of non-PSR-7 method filterPath(), the serves to the same propouse of this
     *
     * @see https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Uri.php#L642
     *
     * @param string $path The path string to be normalized
     * @return string The path with percent encoded characters
     */
    protected function normalizePath($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     * @return static A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('URI path is expected to be a string');
        }
        $clone = clone $this;
        $clone->path = $this->normalizePath($path) ?: null;

        return $clone;
    }

    /**
     * Normalizes the given query string
     *
     * This method is used from Slim Framework URI implementation
     * of non-PSR-7 method filterPath(), the serves to the same propouse of this
     *
     * @see https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Uri.php#L715
     *
     * @param string $query The query string to be normalized
     * @return string The normalized query string in percent encoded characters
     */
    protected function normalizeQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return static A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException('The URI query is expected to be a string');
        }
        $clone = clone $this;
        $clone->query = $query ? $this->normalizeQuery(ltrim($query, '?')) : null;

        return $clone;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        if (!is_string($fragment)) {
            throw new \InvalidArgumentException('The URI fragment is expected to be a string');
        }
        $clone = clone $this;
        $clone->fragment = $fragment ? $this->normalizeQuery(ltrim($fragment, '#')) : null;

        return $clone;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $uri = $scheme ? $scheme . ':' : '';
        $uri .= $authority ? '//' . $authority : '';
        $uri .= $path ? '/' . ltrim($path, '/') : '';
        $uri .= $query ? '?' . $query : '';
        $uri .= $fragment ? '#' . $fragment : '';

        return $uri;
    }
}
