<?php

namespace Pingpp\Util;

use Pingpp\Object;
use stdClass;

abstract class Util
{
    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     *
     * @param array|mixed $array
     * @return boolean True if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array))
            return false;

        // TODO: generally incorrect, but it's correct given Pingpp's response
        foreach (array_keys($array) as $k) {
            if (!is_numeric($k))
                return false;
        }
        return true;
    }

    /**
     * Recursively converts the PHP Pingpp object to an array.
     *
     * @param array $values The PHP Pingpp object to convert.
     * @param bool
     * @return array
     */
    public static function convertPingppObjectToArray($values, $keep_object = false)
    {
        $results = array();
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof Object) {
                $results[$k] = $keep_object ? $v->__toStdObject(true) : $v->__toArray(true);
            } else if (is_array($v)) {
                $results[$k] = self::convertPingppObjectToArray($v, $keep_object);
            } else {
                $results[$k] = $v;
            }
        }
        return $results;
    }

    /**
     * Recursively converts the PHP Pingpp object to an stdObject.
     *
     * @param array $values The PHP Pingpp object to convert.
     * @return array
     */
    public static function convertPingppObjectToStdObject($values)
    {
        $results = new stdClass;
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof Object) {
                $results->$k = $v->__toStdObject(true);
            } else if (is_array($v)) {
                $results->$k = self::convertPingppObjectToArray($v, true);
            } else {
                $results->$k = $v;
            }
        }
        return $results;
    }

    /**
     * Converts a response from the Pingpp API to the corresponding PHP object.
     *
     * @param stdObject $resp The response from the Pingpp API.
     * @param array $opts
     * @return Object|array
     */
    public static function convertToPingppObject($resp, $opts)
    {
        $types = array(
            'red_envelope'=>'Pingpp\\RedEnvelope',
            'charge' => 'Pingpp\\Charge',
            'list' => 'Pingpp\\Collection',
            'refund' => 'Pingpp\\Refund',
            'event' => 'Pingpp\\Event',
            'transfer' => 'Pingpp\\Transfer',
            'customer' => 'Pingpp\\Customer',
            'card' => 'Pingpp\\Card',
            'sms_code' => 'Pingpp\\SmsCode',
            'card_info' => 'Pingpp\\CardInfo',
            'token' => 'Pingpp\\Token'
        );
        if (self::isList($resp)) {
            $mapped = array();
            foreach ($resp as $i)
                array_push($mapped, self::convertToPingppObject($i, $opts));
            return $mapped;
        } else if (is_object($resp)) {
            if (isset($resp->object) 
                && is_string($resp->object)
                && isset($types[$resp->object])) {
                    $class = $types[$resp->object];
                } else {
                    $class = 'Pingpp\\Object';
                }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    public static function getRequestHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @returns string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (is_string($value)
            && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8") {
                return utf8_encode($value);
            } else {
                return $value;
            }
    }
}
