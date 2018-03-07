<?php

namespace Pingpp;

class Customer extends ApiResource
{
    /**
     * @param string $id The ID of the customer to retrieve.
     * @param array|string|null $options
     *
     * @return Customer
     */
    public static function retrieve($id, $options = null)
    {
        return self::_retrieve($id, $options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of Customers.
     */
    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return Customer The created customer.
     */
    public static function create($params = null, $options = null)
    {
        return self::_create($params, $options);
    }

    /**
     * @param array|string|null $options
     *
     * @return Customer The saved customer.
     */
    public function save($options = null)
    {
        return $this->_save($options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return Customer The deleted customer.
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    
}
