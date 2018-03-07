<?php

namespace Pingpp;

class RedEnvelope extends ApiResource
{
    /**
     * This is a special case because the red envelope endpoint has an
     *    underscore in it. The parent `className` function strips underscores.
     *
     * @return string The name of the class.
     */
    public static function className()
    {
        return 'red_envelope';
    }

    /**
     * @param string $id The ID of the redEnvelope to retrieve.
     * @param array|string|null $options
     *
     * @return RedEnvelope
     */
    public static function retrieve($id, $options = null)
    {
        return self::_retrieve($id, $options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of RedEnvelope.
     */
    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return RedEnvelope The created redEnvelope.
     */
    public static function create($params = null, $options = null)
    {
        return self::_create($params, $options);
    }

    /**
     * @param array|string|null $options
     *
     * @return RedEnvelope The saved redEnvelope.
     */
    public function save($options = null)
    {
        return $this->_save($options);
    }
}
