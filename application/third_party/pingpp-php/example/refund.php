<?php
/* *
 * Ping++ Server SDK
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写, 并非一定要使用该代码。
 * 该代码仅供学习和研究 Ping++ SDK 使用，只是提供一个参考。
 */

require_once(dirname(__FILE__) . '/../init.php');

\Pingpp\Pingpp::setApiKey('sk_test_ibbTe5jLGCi5rzfH4OqPW9KC');
$ch = \Pingpp\Charge::retrieve('CHARGE_ID');
$ch->refunds->create(
    array(
        'amount' => 10,
        'description' => 'Your Descripton'
    )
);
