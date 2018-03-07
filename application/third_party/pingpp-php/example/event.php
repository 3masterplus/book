<?php
/**
 * Created by PhpStorm.
 * User: shenlin
 * Date: 15/5/14
 * Time: 17:30
 */

require_once(dirname(__FILE__) . '/../init.php');



\Pingpp\Pingpp::setApiKey('sk_test_ibbTe5jLGCi5rzfH4OqPW9KC');

//查询指定的 event 对象
\Pingpp\Event::retrieve('evt_zRFRk6ekazsH7t7yCqEeovhk');

//查询 event 列表
\Pingpp\Event::all(array('type'=>'charge.succeeded'));
