<?php
/* *
 * Ping++ Server SDK
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写, 并非一定要使用该代码。
 * 该代码仅供学习和研究 Ping++ SDK 使用，只是提供一个参考。
*/

require_once(dirname(__FILE__) . '/../init.php');

/*
 * To set your key
 * key:the livekey or testkey from your app message from pingpp on the dashboard
 */
\Pingpp\Pingpp::setApiKey('sk_test_ibbTe5jLGCi5rzfH4OqPW9KC');
try {
    /*
     * To Create RedEnvelope
     */
    $red = \Pingpp\RedEnvelope::create(
        array(
            'subject'     => 'Your Subject',
            'body'        => 'Your Body',
            'amount'      => 100,
            'order_no'    => '1234567890',
            'currency'    => 'cny',
            'extra'       => array(
                'nick_name' => 'Nick Name',
                'send_name' => 'Send Name'
            ),
            'recipient'   => 'Openid',
            'channel'     => 'wx_pub',
            'app'         => array('id' => 'app_1Gqj58ynP0mHeX1q'),
            'description' => 'Your Description'
        )
    );
    echo $red;
} catch (\Pingpp\Error\Base $e) {
    header('Status: ' . $e->getHttpStatus());
    echo($e->getHttpBody());
}
