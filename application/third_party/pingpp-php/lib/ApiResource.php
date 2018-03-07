<?php

namespace Pingpp;

abstract class ApiResource extends Object
{
    private static $HEADERS_TO_PERSIST = array('Pingpp-Version' => true);

    public static function baseUrl()
    {
        return Pingpp::$apiBase;
    }

    /**
     * @return ApiResource The refreshed resource.
     */
    public function refresh()
    {
        $requestor = new ApiRequestor($this->_opts->apiKey, static::baseUrl());
        $url = $this->instanceUrl();

        list($response, $this->_opts->apiKey) = $requestor->request(
            'get',
            $url,
            $this->_retrieveOptions,
            $this->_opts->headers
        );
        $this->refreshFrom($response, $this->_opts);
        return $this;
    }

    /**
     * @return string The name of the class, with namespacing and underscores
     *    stripped.
     */
    public static function className()
    {
        $class = get_called_class();
        // Useful for namespaces: Foo\Charge
        if ($postfix = strrchr($class, '\\')) {
            $class = substr($postfix, 1);
        }
        // Useful for underscored 'namespaces': Foo_Charge
        if ($postfixFakeNamespaces = strrchr($class, '')) {
            $class = $postfixFakeNamespaces;
        }
        if (substr($class, 0, strlen('Pingpp')) == 'Pingpp') {
            $class = substr($class, strlen('Pingpp'));
        }
        $class = str_replace('_', '', $class);
        $name = urlencode($class);
        $name = strtolower($name);
        return $name;
    }

    /**
     * @return string The endpoint URL for the given class.
     */
    public static function classUrl()
    {
        $base = static::className();
        return "/v1/${base}s";
    }

    /**
     * @return string The full API URL for this API resource.
     */
    public function instanceUrl()
    {
        $id = $this['id'];
        $class = get_called_class();
        if ($id === null) {
            $message = "Could not determine which URL to request: "
                . "$class instance has invalid ID: $id";
            throw new Error\InvalidRequest($message, null);
        }
        $id = Util\Util::utf8($id);
        $base = static::classUrl();
        $extn = urlencode($id);
        return "$base/$extn";
    }

    private static function _validateParams($params = null)
    {
        if ($params && !is_array($params)) {
            $message = "You must pass an array as the first argument to Pingpp API "
               . "method calls.";
            throw new Error\Api($message);
        }
    }

    protected function _request($method, $url, $params = array(), $options = null)
    {
        $opts = $this->_opts->merge($options);
        return static::_staticRequest($method, $url, $params, $opts);
    }

    protected static function _staticRequest($method, $url, $params, $options)
    {
        $opts = Util\RequestOptions::parse($options);
        $requestor = new ApiRequestor($opts->apiKey, static::baseUrl());
        list($response, $opts->apiKey) = $requestor->request($method, $url, $params, $opts->headers);
        foreach ($opts->headers as $k => $v) {
            if (!array_key_exists($k, self::$HEADERS_TO_PERSIST)) {
                unset($opts->headers[$k]);
            }
        }
        return array($response, $opts);
    }

    protected static function _retrieve($id, $options = null)
    {
        $opts = Util\RequestOptions::parse($options);
        $instance = new static($id, $opts);
        $instance->refresh();
        return $instance;
    }

    protected static function _all($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('get', $url, $params, $options);
        return Util\Util::convertToPingppObject($response, $opts);
    }

    protected static function _create($params = null, $options = null)
    {
        self::_validateParams($params);
        $base = static::baseUrl();
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
        return Util\Util::convertToPingppObject($response, $opts);
    }

    protected function _save($options = null)
    {
        $params = $this->serializeParameters();
        if (count($params) > 0) {
            $url = $this->instanceUrl();
            list($response, $opts) = $this->_request('post', $url, $params, $options);
            $this->refreshFrom($response, $opts);
        }
        return $this;
    }

    protected function _delete($params = null, $options = null)
    {
        self::_validateParams($params);

        $url = $this->instanceUrl();
        list($response, $opts) = $this->_request('delete', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
