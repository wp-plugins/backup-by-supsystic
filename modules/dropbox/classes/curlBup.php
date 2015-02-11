<?php

/**
 * Class curlBup
 * @package Dropbox\Classesy
 */
class curlBup {
    /**
     * cURL handle
     * @var resource
     */
    private $handle;

    /**
     * URL to send request
     * @var string
     */
    private $url;

    /**
     * HTTP headers
     * @var array
     */
    private $headers;

    /**
     * Number of seconds to spend attempting to connect
     * @var int
     */
    private $connectionTimeout;

    /**
     * Constructor
     * @param string  $url               Request URL
     * @param array   $headers           An array of header to send with request
     * @param integer $connectionTimeout Number of seconds to spend attempting to connect
     */
    public function __construct($url = null, array $headers = array(), $connectionTimeout = 20)
    {
        $this->init($url);
        $this->setHeaders($headers);
        $this->setConnectionTimeout($connectionTimeout);

        // Force SSL and use our own certificate list.
        $this->setOption('SSL_VERIFYPEER', true);   // Enforce certificate validation
        $this->setOption('SSL_VERIFYHOST', 2);      // Enforce hostname validation
        $this->setOption('SSLVERSION', 1);          // Enforce SSL v3.

        // Only allow ciphersuites supported by Dropbox
		$curlVersion = curl_version();
		$curlSslBackend = $curlVersion['ssl_version'];
		// See more about this code - here https://github.com/dropbox/dropbox-sdk-php/commit/05ad82740afd576073bfdb34136ef63d4174167e
		if(substr_compare($curlSslBackend, "NSS/", 0, strlen("NSS/")) === 0) {
			// Can't figure out how to reliably set ciphersuites for NSS.
		} else {
			$this->setOption('SSL_CIPHER_LIST',
				'ECDHE-RSA-AES256-GCM-SHA384:'.
				'ECDHE-RSA-AES128-GCM-SHA256:'.
				'ECDHE-RSA-AES256-SHA384:'.
				'ECDHE-RSA-AES128-SHA256:'.
				'ECDHE-RSA-AES256-SHA:'.
				'ECDHE-RSA-AES128-SHA:'.
				'ECDHE-RSA-RC4-SHA:'.
				'DHE-RSA-AES256-GCM-SHA384:'.
				'DHE-RSA-AES128-GCM-SHA256:'.
				'DHE-RSA-AES256-SHA256:'.
				'DHE-RSA-AES128-SHA256:'.
				'DHE-RSA-AES256-SHA:'.
				'DHE-RSA-AES128-SHA:'.
				'AES256-GCM-SHA384:'.
				'AES128-GCM-SHA256:'.
				'AES256-SHA256:'.
				'AES128-SHA256:'.
				'AES256-SHA:'.
				'AES128-SHA'
			);
		}
        $this->setOption('CAINFO', dirname(__FILE__).'/certs/trusted-certs.crt'); // Certificate file location
        $this->setOption('CAPATH', dirname(__FILE__).'/certs/'); // Certificate folder. Need to specify it to avoid using system default certs on some platforms

        // Limit vulnerability surface area.  Supported in cURL 7.19.4+
        if (defined('CURLOPT_PROTOCOLS')) $this->setOption('PROTOCOLS', CURLPROTO_HTTPS);
        if (defined('CURLOPT_REDIR_PROTOCOLS')) $this->setOption('REDIR_PROTOCOLS', CURLPROTO_HTTPS);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        curl_close($this->handle);
    }

    /**
     * Initialize cURL
     * @param string $url Request URL
     */
    public function init($url = null)
    {
        $this->url    = $url;
        $this->handle = curl_init($url);
    }

    /**
     * Execute cURL request
     * @throws RuntimeException
     * @return mixed
     */
    public function exec()
    {
        $this->setOption('HttpHeader', $this->headers);
        $this->setOption('UserAgent',  'Backup By Supsystic Dropbox Module/0.3.6');
        $this->setOption('ReturnTransfer', true);
        $this->setOption('AutoReferer', true);

        $response = curl_exec($this->handle);

        if (!$response) {
            throw new RuntimeException(curl_error($this->handle));
        }

        return $response;
    }

    /**
     * Set cURL request URL
     * @param  string $url Request URL
     * @return curlBup
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->setOption('URL', $url);
        return $this;
    }

    /**
     * Get current URL to send request
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Configure cURL for POST request
     * @param array $fields An array of data to send with request
     */
    public function setPostRequest(array $fields = array())
    {
        $this->setOption('POST', true);
        $this->setOption('PostFields', self::buildQuery($fields));
    }

    /**
     * Configure cURL for GET request
     * @param array $fields
     */
    public function setGetRequest(array $fields = array())
    {
        $url = rtrim($this->url, '/?');

        $this->setUrl(sprintf('%s/?%s', $url, self::buildQuery($fields, true)));
    }

    public function setPutRequest(array $fields, $body)
    {
        $this->setOption('CustomRequest', 'PUT');

        if (count($fields)) {
            $this->setUrl(rtrim($this->url, '/?') . '?' . self::buildQuery($fields));
        }

        if ($body) {
            $this->setOption('PostFields', $body);
        }
    }

    /**
     * Set curl option
     * @param  string $option cURL option without CURL prefix
     * @param  mixed  $value  Option value
     * @return curlBup
     */
    public function setOption($option, $value)
    {
        $optionConst = sprintf('CURLOPT_%s', strtoupper($option));
        curl_setopt($this->handle, constant($optionConst), $value);

        return $this;
    }

    /**
     * Add header to stack
     * @param  string $name  Header name
     * @param  string $value Header value
     * @return curlBup
     */
    public function setHeader($name, $value)
    {
        $this->headers[] = sprintf('%s: %s', $name, $value);
        return $this;
    }

    /**
     * Set an array of headers
     * @param  array $headers An numeric array of HTTP headers
     * @return curlBup
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Returns an array of headers
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set cURL connection timeout
     * @param int $connectionTimeout
     * @return curlBup
     */
    public function setConnectionTimeout($connectionTimeout)
    {
        $this->connectionTimeout = intval($connectionTimeout);
        return $this;
    }

    /**
     * Get current connection timeout value
     * @return int
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

    /**
     * Set access_token to request to make authorization api calls
     * @param string $accessToken
     */
    public function setAuthorization($accessToken)
    {
        $this->headers[] = "Authorization: Bearer $accessToken";
    }

    /**
     * Create new instance and prepare to post request
     * @param string $url
     * @param array $fields
     * @return curlBup
     */
    public static function createPostRequest($url, array $fields = array())
    {
        $dropboxCurl = new self($url);
        $dropboxCurl->setPostRequest($fields);

        return $dropboxCurl;
    }

    /**
     * Create new instance and prepare to GET request
     * @param string $url
     * @param array $fields
     * @return curlBup
     */
    public static function createGetRequest($url, array $fields = array())
    {
        $dropboxCurl = new self($url);
        $dropboxCurl->setGetRequest($fields);

        return $dropboxCurl;
    }

    /**
     * @return curlBup
     */
    public static function createPutRequest($url, array $fields = array(), $body = null)
    {
        $dbx = new self($url);
        $dbx->setPutRequest($fields, $body);

        return $dbx;
    }

    /**
     * Build query string
     * @param array $fields An associative array of fields to build query
     * @param bool  $encode Use urlencode function for array values or not
     * @return string
     */
    public static function buildQuery(array $fields, $encode = false)
    {
        $query = null;

        if (count($fields) < 1) {
            return '';
        }

        if ($encode && function_exists('http_build_query')) {
            return http_build_query($fields);
        }

        foreach ($fields as $field => $value) {
            if ($encode) {
                $value = urlencode($value);
            }

            $query .= sprintf('%s=%s&', $field, $value);
        }

        return rtrim($query, '&');
    }
}
