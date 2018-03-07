<?php

namespace Pingpp\Error;

class Channel extends Base
{
    public function __construct($message, $errcode, $param, $httpStatus=null,
        $httpBody=null, $jsonBody=null
    )
    {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
        $this->errcode = $errcode;
        $this->param = $param;
    }
}
