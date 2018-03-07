<?php

if (!function_exists('curl_init')) {
  throw new Exception('Pingpp needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Pingpp needs the JSON PHP extension.');
}
if (!function_exists('mb_detect_encoding')) {
  throw new Exception('Pingpp needs the Multibyte String PHP extension.');
}

// Pingpp singleton
require(dirname(__FILE__) . '/lib/Pingpp.php');

// Utilities
require(dirname(__FILE__) . '/lib/Util/Util.php');
require(dirname(__FILE__) . '/lib/Util/Set.php');
require(dirname(__FILE__) . '/lib/Util/RequestOptions.php');

// Errors
require(dirname(__FILE__) . '/lib/Error/Base.php');
require(dirname(__FILE__) . '/lib/Error/Api.php');
require(dirname(__FILE__) . '/lib/Error/ApiConnection.php');
require(dirname(__FILE__) . '/lib/Error/Authentication.php');
require(dirname(__FILE__) . '/lib/Error/InvalidRequest.php');
require(dirname(__FILE__) . '/lib/Error/RateLimit.php');
require(dirname(__FILE__) . '/lib/Error/Channel.php');

// Plumbing
require(dirname(__FILE__) . '/lib/Object.php');
require(dirname(__FILE__) . '/lib/ApiRequestor.php');
require(dirname(__FILE__) . '/lib/ApiResource.php');
require(dirname(__FILE__) . '/lib/SingletonApiResource.php');
require(dirname(__FILE__) . '/lib/AttachedObject.php');
require(dirname(__FILE__) . '/lib/Collection.php');

// Pingpp API Resources
require(dirname(__FILE__) . '/lib/Charge.php');
require(dirname(__FILE__) . '/lib/Refund.php');
require(dirname(__FILE__) . '/lib/RedEnvelope.php');
require(dirname(__FILE__) . '/lib/Event.php');
require(dirname(__FILE__) . '/lib/Transfer.php');
require(dirname(__FILE__) . '/lib/Customer.php');
require(dirname(__FILE__) . '/lib/Source.php');
require(dirname(__FILE__) . '/lib/Card.php');
require(dirname(__FILE__) . '/lib/Token.php');
require(dirname(__FILE__) . '/lib/CardInfo.php');
require(dirname(__FILE__) . '/lib/SmsCode.php');

// wx_pub OAuth 2.0 method
require(dirname(__FILE__) . '/lib/WxpubOAuth.php');
