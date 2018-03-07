<?php

namespace Pingpp;

class SmsCode extends ApiResource
{
    /**
     * This is a special case because the sms code endpoint has an
     *    underscore in it. The parent `className` function strips underscores.
     *
     * @return string The name of the class.
     */
    public static function className()
    {
        return 'sms_code';
    }

    /**
     * @param string $id The ID of the sms code to retrieve.
     * @param array|string|null $opts
     *
     * @return SMS Code
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }
}