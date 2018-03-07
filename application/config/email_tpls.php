<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Email template
| -------------------------------------------------------------------------
| 该文件用于定义所有的邮件模版
|
*/

$config['email_main_from']      = 'do_not_reply@zhiler.com';
$config['email_main_from_name'] = '知乐';


$config['email_tpls'] = array(

    /**
     * 注册
     */
    'register' => array(
            'from' => 'do_not_reply@zhiler.com',
            'from_name' => '知乐',
            'subject' => <<<EOT
欢迎加入知乐网
EOT,
            'message' => <<<EOT
尊敬的%s：您好！

感谢您注册知乐网！

请您在48小时内点击下方链接验证您的电子邮箱：
%s

您在使用知乐网或移动客户端的过程中遇到任何问题，请及时联系我们。

支持邮箱： support@tmtpost.com
联系电话：＋86 10 59231559 转 221

感谢您对知乐网的信任与支持！

知乐
EOT

    ),


);
