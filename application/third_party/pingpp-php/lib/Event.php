<?php

namespace Pingpp;

class Event extends ApiResource
{
    /**
     * @param string $id The ID of the event to retrieve.
     * @param array|string|null $options
     *
     * @return Event
     */
    public static function retrieve($id, $options = null)
    {
        return self::_retrieve($id, $options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of Events.
     */
    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }
}
