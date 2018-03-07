<?php
/* *
 * Ping++ Server SDK
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写, 并非一定要使用该代码。
 * 该代码仅供学习和研究 Ping++ SDK 使用，只是提供一个参考。
 */

$input_data = json_decode(file_get_contents('php://input'), true);
if($input_data['type'] == 'charge.succeeded')
{
    //TODO update database
    http_response_code(200);// PHP 5.4 or greater

}

else if($input_data['type'] == 'refund.succeeded')
{
    //TODO update database
    http_response_code(200);// PHP 5.4 or greater
}
else
{
    //TODO update database
    http_response_code(500);// PHP 5.4 or greater
}

