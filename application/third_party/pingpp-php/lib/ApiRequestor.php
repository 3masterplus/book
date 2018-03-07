<?php

namespace Pingpp;

class ApiRequestor
{
    /**
     * @var string $apiKey The API key that's to be used to make requests.
     */
    public $apiKey;

    private $_apiBase;

    private static $_preFlight = array();

    private static function blacklistedCerts()
    {
        return array(
        );
    }

    public function __construct($apiKey = null, $apiBase = null)
    {
        $this->_apiKey = $apiKey;
        if (!$apiBase) {
            $apiBase = Pingpp::$apiBase;
        }
        $this->_apiBase = $apiBase;
    }

    private static function _encodeObjects($d, $is_post = false)
    {
        if ($d instanceof ApiResource) {
            return Util\Util::utf8($d->id);
        } else if ($d === true && !$is_post) {
            return 'true';
        } else if ($d === false && !$is_post) {
            return 'false';
        } else if (is_array($d)) {
            $res = array();
            foreach ($d as $k => $v)
                $res[$k] = self::_encodeObjects($v, $is_post);
            return $res;
        } else {
            return Util\Util::utf8($d);
        }
    }

    /**
     * @param array $arr An map of param keys to values.
     * @param string|null $prefix (It doesn't look like we ever use $prefix...)
     *
     * @returns string A querystring, essentially.
     */
    public static function encode($arr, $prefix = null)
    {
        if (!is_array($arr)) {
            return $arr;
        }

        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v)) {
                continue;
            }

            if ($prefix && $k && !is_int($k)) {
                $k = $prefix."[".$k."]";
            } else if ($prefix) {
                $k = $prefix."[]";
            }

            if (is_array($v)) {
                $r[] = self::encode($v, $k, true);
            } else {
                $r[] = urlencode($k)."=".urlencode($v);
            }
        }

        return implode("&", $r);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $params
     * @param array|null $headers
     *
     * @return array An array whose first element is the response and second
     *    element is the API key used to make the request.
     */
    public function request($method, $url, $params = null, $headers = null)
    {
        if (!$params) {
            $params = array();
        }
        if (!$headers) {
            $headers = array();
        }
        list($rbody, $rcode, $myApiKey) = $this->_requestRaw($method, $url, $params, $headers);
        $resp = $this->_interpretResponse($rbody, $rcode);
        return array($resp, $myApiKey);
    }


    /**
     * @param string $rbody A JSON string.
     * @param int $rcode
     * @param array $resp
     *
     * @throws InvalidRequestError if the error is caused by the user.
     * @throws AuthenticationError if the error is caused by a lack of
     *    permissions.
     * @throws ApiError otherwise.
     */
    public function handleApiError($rbody, $rcode, $resp)
    {
        if (!is_object($resp) || !isset($resp->error)) {
            $msg = "Invalid response object from API: $rbody "
                ."(HTTP response code was $rcode)";
            throw new Error\Api($msg, $rcode, $rbody, $resp);
        }

        $error = $resp->error;
        $msg = isset($error->message) ? $error->message : null;
        $param = isset($error->param) ? $error->param : null;
        $code = isset($error->code) ? $error->code : null;

        switch ($rcode) {
            case 400:
                if ($code == 'rate_limit') {
                    throw new Error\RateLimit(
                        $msg, $param, $rcode, $rbody, $resp
                    );
                }
            case 404:
                throw new Error\InvalidRequest(
                    $msg, $param, $rcode, $rbody, $resp
                );
            case 401:
                throw new Error\Authentication($msg, $rcode, $rbody, $resp);
            case 402:
                throw new Error\Channel(
                    $msg, $code, $param, $rcode, $rbody, $resp
                );
            default:
                throw new Error\Api($msg, $rcode, $rbody, $resp);
        }
    }

    private function _requestRaw($method, $url, $params, $headers)
    {
        if (!array_key_exists($this->_apiBase, self::$_preFlight) ||
            !self::$_preFlight[$this->_apiBase]) {
            self::$_preFlight[$this->_apiBase] = $this->checkSslCert($this->_apiBase);
        }

        $myApiKey = $this->_apiKey;
        if (!$myApiKey) {
            $myApiKey = Pingpp::$apiKey;
        }

        if (!$myApiKey) {
            $msg = 'No API key provided.  (HINT: set your API key using '
                . '"Pingpp::setApiKey(<API-KEY>)".  You can generate API keys from '
                . 'the Pingpp web interface.  See https://pingxx.com/document/api for '
                . 'details, or email support@pingxx.com if you have any questions.';
            throw new Error\Authentication($msg);
        }

        $absUrl = $this->_apiBase . $url;
        $params = self::_encodeObjects($params, $method == 'post');
        $langVersion = phpversion();
        $uname = php_uname();
        $ua = array(
            'bindings_version' => Pingpp::VERSION,
            'lang' => 'php',
            'lang_version' => $langVersion,
            'publisher' => 'pingplusplus',
            'uname' => $uname
        );
        $defaultHeaders = array(
            'X-Pingpp-Client-User-Agent' => json_encode($ua),
            'User-Agent' => 'Pingpp/v1 PhpBindings/' . Pingpp::VERSION,
            'Authorization' => 'Bearer ' . $myApiKey
        );
        if (Pingpp::$apiVersion) {
            $defaultHeaders['Pingplusplus-Version'] = Pingpp::$apiVersion;
        }
        if ($method == 'post') {
            $defaultHeaders['Content-type'] = 'application/json;charset=UTF-8';
        }
        $requestHeaders = Util\Util::getRequestHeaders();
        if (isset($requestHeaders['Pingpp-Sdk-Version'])) {
            $defaultHeaders['Pingpp-Sdk-Version'] = $requestHeaders['Pingpp-Sdk-Version'];
        }
        if (isset($requestHeaders['Pingpp-One-Version'])) {
            $defaultHeaders['Pingpp-One-Version'] = $requestHeaders['Pingpp-One-Version'];
        }

        $combinedHeaders = array_merge($defaultHeaders, $headers);

        $rawHeaders = array();

        foreach ($combinedHeaders as $header => $value) {
            $rawHeaders[] = $header . ': ' . $value;
        }

        list($rbody, $rcode) = $this->_curlRequest(
            $method,
            $absUrl,
            $rawHeaders,
            $params
        );
        return array($rbody, $rcode, $myApiKey);
    }

    private function _interpretResponse($rbody, $rcode)
    {
        try {
            $resp = json_decode($rbody);
        } catch (Exception $e) {
            $msg = "Invalid response body from API: $rbody "
                . "(HTTP response code was $rcode)";
            throw new Error\Api($msg, $rcode, $rbody);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleApiError($rbody, $rcode, $resp);
        }
        return $resp;
    }

    private function _curlRequest($method, $absUrl, $headers, $params)
    {
        $curl = curl_init();
        $method = strtolower($method);
        $opts = array();
        if ($method == 'get') {
            $opts[CURLOPT_HTTPGET] = 1;
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "$absUrl?$encoded";
            }
        } else if ($method == 'post') {
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);
        } else if ($method == 'delete') {
            $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "$absUrl?$encoded";
            }
        } else {
            throw new Error\Api("Unrecognized method $method");
        }

        $absUrl = Util\Util::utf8($absUrl);
        $opts[CURLOPT_URL] = $absUrl;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 30;
        $opts[CURLOPT_TIMEOUT] = 80;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        if (!Pingpp::$verifySslCerts) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
        }

        curl_setopt_array($curl, $opts);
        $rbody = curl_exec($curl);

        if (!defined('CURLE_SSL_CACERT_BADFILE')) {
            define('CURLE_SSL_CACERT_BADFILE', 77);  // constant not defined in PHP
        }

        $errno = curl_errno($curl);
        if ($errno == CURLE_SSL_CACERT ||
            $errno == CURLE_SSL_PEER_CERTIFICATE ||
            $errno == CURLE_SSL_CACERT_BADFILE) {
                array_push(
                    $headers,
                    'X-Pingpp-Client-Info: {"ca":"using Pingpp-supplied CA bundle"}'
                );
                $cert = $this->caBundle();
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_CAINFO, $cert);
                $rbody = curl_exec($curl);
            }

        if ($rbody === false) {
            $errno = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            $this->handleCurlError($errno, $message);
        }

        $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return array($rbody, $rcode);
    }

    /**
     * @param number $errno
     * @param string $message
     * @throws ApiConnectionError
     */
    public function handleCurlError($errno, $message)
    {
        $apiBase = Pingpp::$apiBase;
        switch ($errno) {
        case CURLE_COULDNT_CONNECT:
        case CURLE_COULDNT_RESOLVE_HOST:
        case CURLE_OPERATION_TIMEOUTED:
            $msg = "Could not connect to Pingpp ($apiBase).  Please check your "
                . "internet connection and try again.  If this problem persists, "
                . "you should check Pingpp's service status at "
                . "https://pingxx.com, or";
            break;
        case CURLE_SSL_CACERT:
        case CURLE_SSL_PEER_CERTIFICATE:
            $msg = "Could not verify Pingpp's SSL certificate.  Please make sure "
                . "that your network is not intercepting certificates.  "
                . "(Try going to $apiBase in your browser.)  "
                . "If this problem persists,";
            break;
        default:
            $msg = "Unexpected error communicating with Pingpp.  "
                . "If this problem persists,";
        }
        $msg .= " let us know at support@pingxx.com.";

        $msg .= "\n\n(Network error [errno $errno]: $message)";
        throw new Error\ApiConnection($msg);
    }

    private function checkSslCert($url)
    {
        /* Preflight the SSL certificate presented by the backend. This isn't 100%
         * bulletproof, in that we're not actually validating the transport used to
         * communicate with Pingpp, merely that the first attempt to does not use a
         * revoked certificate.

         * Unfortunately the interface to OpenSSL doesn't make it easy to check the
         * certificate before sending potentially sensitive data on the wire. This
         * approach raises the bar for an attacker significantly.
         */

        if (!function_exists('stream_context_get_params') ||
            !function_exists('stream_socket_enable_crypto')) {
            error_log(
                'Warning: This version of PHP is too old to check SSL certificates '.
                'correctly. Pingpp cannot guarantee that the server has a '.
                'certificate which is not blacklisted'
            );
            return true;
        }

        $url = parse_url($url);
        $port = isset($url["port"]) ? $url["port"] : 443;
        $url = "ssl://{$url["host"]}:{$port}";

        $sslContext = stream_context_create(
            array('ssl' => array(
                'capture_peer_cert' => true,
                'verify_peer'   => true,
                'cafile'        => $this->caBundle(),
            ))
        );
        $result = stream_socket_client(
            $url, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $sslContext
        );
        if ($errno !== 0) {
            $apiBase = Pingpp::$apiBase;
            throw new Error\ApiConnection(
                'Could not connect to Pingpp ($apiBase).  Please check your '.
                'internet connection and try again.  If this problem persists, '.
                'you should check Pingpp\'s service status at '.
                'https://pingxx.com. Reason was: '.$errstr
            );
        }

        $params = stream_context_get_params($result);

        $cert = $params['options']['ssl']['peer_certificate'];

        openssl_x509_export($cert, $pem_cert);

        if (self::isBlackListed($pem_cert)) {
            throw new Error\ApiConnection(
                'Invalid server certificate. You tried to connect to a server '.
                'that has a revoked SSL certificate, which means we cannot '.
                'securely send data to that server.  Please email '.
                'support@pingxx.com if you need help connecting to the '.
                'correct API server.'
            );
        }

        return true;
    }

    /* Checks if a valid PEM encoded certificate is blacklisted
     * @return boolean
     */
    public static function isBlackListed($certificate)
    {
        $certificate = trim($certificate);
        $lines = explode("\n", $certificate);

        // Kludgily remove the PEM padding
        array_shift($lines); array_pop($lines);

        $der_cert = base64_decode(implode("", $lines));
        $fingerprint = sha1($der_cert);
        return in_array($fingerprint, self::blacklistedCerts());
    }

    private function caBundle()
    {
        return dirname(__FILE__) . '/../data/ca-certificates.crt';
    }
}
