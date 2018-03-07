<?php

namespace Pingpp;

class Source extends ApiResource
{
    /**
     * @return string The instance URL for this resource. It needs to be special
     *    cased because it doesn't fit into the standard resource pattern.
     */
    public function instanceUrl()
    {
        $id = $this['id'];
        if (!$id) {
            $class = get_class($this);
            $msg = "Could not determine which URL to request: $class instance "
             . "has invalid ID: $id";
            throw new Error\InvalidRequest($msg, null);
        }
        if ($this['customer']) {
            $parent = $this['customer'];
            $base = Customer::classUrl();
            $path = 'sources';
        } else {
            return null;
        }
        $parent = Util\Util::utf8($parent);
        $id = Util\Util::utf8($id);
        $parentExtn = urlencode($parent);
        $extn = urlencode($id);
        return "$base/$parentExtn/$path/$extn";
    }

    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return Source The deleted source.
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    /**
     * @param array|string|null $opts
     *
     * @return Source The saved source.
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }
}