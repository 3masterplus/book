<?php

namespace Pingpp;

class CardInfo extends ApiResource
{
    /**
     * This is a special case because the card info endpoint has an
     *    underscore in it. The parent `className` function strips underscores.
     *
     * @return string The name of the class.
     */
    public static function className()
    {
        return 'card_info';
    }

    /**
     * @return string The endpoint URL for the given class.
     */
    public static function classUrl()
    {
        $base = static::className();
        return "/v1/${base}";
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return CardInfo The queried cardInfo.
     */
    public static function query($params = null, $options = null)
    {
        return self::_create($params, $options);
    }
}
