<?php
/* *
 * Ping++ Server SDK
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写, 并非一定要使用该代码。
 * 该代码仅供学习和研究 Ping++ SDK 使用，只是提供一个参考。
*/

require_once(dirname(__FILE__) . '/../init.php');

\Pingpp\Pingpp::setApiKey('sk_test_ibbTe5jLGCi5rzfH4OqPW9KC');
try {
    $tr = \Pingpp\Transfer::create(
        array(
            'amount'   => 100,
            'order_no'  => '123456d7890',
            'currency'  => 'cny',
            'channel'   => 'wx_pub',
            'app'       => array('id' => 'app_1Gqj58ynP0mHeX1q'),
            'type'      => 'b2c',
            'recipient' => 'o9zpMs9jIaLynQY9N6yxcZ',
            'description'=>'testing',
            'extra'=>array('user_name'=>'User Name', 'force_check'=>true)

        )
    );
    echo $tr;
} catch (\Pingpp\Error\Base $e) {
    header('Status: ' . $e->getHttpStatus());
    echo($e->getHttpBody());
}
