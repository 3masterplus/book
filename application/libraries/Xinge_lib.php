<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'/third_party/xinge_push/XingeApp.php';

/**
 * 信鸽推送通用库
 */

class Xinge_lib {
    /*
     * @var app名称
     */
    private $_app_name = "知乐";

    /*
     * @var access_id
    */
    private $_access_id = 2200165704;
    /*
     * @var access_key
    */
    private $_access_key = "I81U5YX91GJV";
    /*
     * @var secret_key
    */
    private $_secret_key = "78dd35ae78e1fc929f23f9b0dcda9419";
    /*
     * @var 设备类型
    */
    private $_device = "ios";

    /*
     * 推送对象
     * @var [type]
    */
    private $_push;
    
    /*
     * @var 苹果推送环境
    */
    private $_ios_environment = XingeApp::IOSENV_DEV;

    /*
     * @var 安卓平台收到消息后的默认跳转activity
    */
    private $_android_defualt_activity = 'com.jiu.push.MainActivity';


    function __construct()
    {
        /*
         * 初始化推送对象
         */
        $this->set_push();
    }

    /**
     * 初始化平台信息
     * @return object
     */
    function set_push()
    {   
        $this->_push = new XingeApp($this->_access_id, $this->_secret_key);
    }

    /**
     * 设置设备类型
     * @param string
     */
    function set_device($device)
    {
        $this->_device = strtolower($device);
    }

    /**
     * 下发IOS设备消息分发器 (自动识别推送平台, 简化调用复杂度)
     */
    function PushSingleDeviceDispatcher($token, $title = NULL, $content = NULL, $expireTime = 86400, $custom = array(), $config = array() )
    {
        if ($this->_device == 'ios' ) {
            $this->PushSingleDeviceIOS($token, $title, $content, $expireTime, $custom, $config);
        }else {
            $this->PushSingleDeviceAndroid($token, $title, $content, $expireTime, $custom, $config);
        }
    }

    //下发IOS设备消息
    function PushSingleDeviceIOS($token, $title = NULL, $content = NULL, $expireTime = 86400, $custom = array(), $config = array() )
    {
        $mess = new MessageIOS();
        $mess->setExpireTime(86400);
        //$mess->setSendTime("2014-03-13 16:00:00");
        $mess->setAlert($content);
        //$mess->setAlert(array('key1'=>'value1'));
        $mess->setBadge(1);
        $mess->setSound("default");
        $mess->setCustom($custom);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        // $raw = '{"xg_max_payload":1,"accept_time":[{"start":{"hour":"20","min":"0"},"end":{"hour":"23","min":"59"}}],"aps":{"alert":"="}}';
        // $mess->setRaw($raw);
        $ret = $this->_push->PushSingleDevice($token, $mess, $this->_ios_environment);
        return $ret;
    }


    //单个设备下发通知消息
    function PushSingleDeviceAndroid($token, $title = NULL, $content = NULL, $expireTime = 86400, $custom = array(), $config = array() )
    {
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setExpireTime(86400);
        //$style = new Style(0);
        #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(0,1,1,0,0);
        $mess->setStyle($style);
        //指定activity
        $action = new ClickAction();
        $action->setActivity($this->_android_defualt_activity);
        $mess->setAction($action);
        $mess->setCustom($custom);
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
        $ret = $this->_push->PushSingleDevice($token, $mess);
        return($ret);
    }


    /**
     * 推送消息给所有的APP设备
     */
    function PushAllDevicesDispatcher($title = NULL, $content = NULL, $expireTime = 86400, $custom = array(), $config = array())
    {
        $this->PushAllDevicesIOS($title, $content, $expireTime, $custom, $config);
        $this->PushAllDevicesAndroid($title, $content, $expireTime, $custom, $config);
    }

    //下发所有设备
    function PushAllDevicesIOS($title = NULL, $content = NULL, $expireTime = 86400, $custom = array(), $config = array())
    {
        $mess = new MessageIOS();
        $mess->setExpireTime(86400);
        //$mess->setSendTime("2014-03-13 16:00:00");
        $mess->setAlert($content);
        //$mess->setAlert(array('key1'=>'value1'));
        $mess->setBadge(1);
        $mess->setSound("default");
        $mess->setCustom($custom);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
       
        $ret = $this->_push->PushAllDevices(0, $mess, $this->_ios_environment);
        return ($ret);
    }

    //下发所有设备
    function PushAllDevicesAndroid($title = NULL, $content = NULL, $expireTime = 86400, $custom = array(), $config = array())
    {
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setExpireTime(86400);
        //$style = new Style(0);
        #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(0,1,1,0,0);
        $mess->setStyle($style);
        //指定activity
        $action = new ClickAction();
        $action->setActivity($this->_android_defualt_activity);
        $mess->setAction($action);
        $mess->setCustom($custom);
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
       
        $ret = $this->_push->PushAllDevices(0, $mess);
        return ($ret);
    }

    








    // //下发单个账号
    // function PushSingleAccount()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $mess = new Message();
    //     $mess->setExpireTime(86400);
    //     $mess->setTitle('title');
    //     $mess->setContent('content');
    //     $mess->setType(Message::TYPE_MESSAGE);
    //     $ret = $push->PushSingleAccount(0, 'joelliu', $mess);
    //     return ($ret);
    // }

    // //下发多个账号， IOS下发多个账号参考DemoPushSingleAccountIOS进行相应修改
    // function PushAccountList()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $mess = new Message();
    //     $mess->setExpireTime(86400);
    //     $mess->setTitle('title');
    //     $mess->setContent('content');
    //     $mess->setType(Message::TYPE_MESSAGE);
    //     $accountList = array('joelliu');
    //     $ret = $push->PushAccountList(0, $accountList, $mess);
    //     return ($ret);
    // }

    // //下发IOS账号消息
    // function PushSingleAccountIOS()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $mess = new MessageIOS();
    //     $mess->setExpireTime(86400);
    //     $mess->setAlert("ios test");
    //     //$mess->setAlert(array('key1'=>'value1'));
    //     $mess->setBadge(1);
    //     $mess->setSound("beep.wav");
    //     $custom = array('key1'=>'value1', 'key2'=>'value2');
    //     $mess->setCustom($custom);
    //     $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    //     $mess->addAcceptTime($acceptTime1);
    //     $ret = $push->PushSingleAccount(0, 'joelliu', $mess, XingeApp::IOSENV_DEV);
    //     return $ret;
    // }


    // //下发标签选中设备
    // function PushTags()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $mess = new Message();
    //     $mess->setExpireTime(86400);
    //     $mess->setTitle('title');
    //     $mess->setContent('content');
    //     $mess->setType(Message::TYPE_MESSAGE);
    //     $tagList = array('Demo3');
    //     $ret = $push->PushTags(0, $tagList, 'OR', $mess);
    //     return ($ret);
    // }

    // //查询消息推送状态
    // function QueryPushStatus()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $pushIdList = array('31','32');
    //     $ret = $push->QueryPushStatus($pushIdList);
    //     return ($ret);
    // }

    // //查询设备数量
    // function QueryDeviceCount()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->QueryDeviceCount();
    //     return ($ret);
    // }

    // //查询标签
    // function QueryTags()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->QueryTags(0,100);
    //     return ($ret);
    // }

    // //查询某个tag下token的数量
    // function QueryTagTokenNum()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->QueryTagTokenNum("tag");
    //     return ($ret);
    // }

    // //查询某个token的标签
    // function QueryTokenTags()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->QueryTokenTags("token");
    //     return ($ret);
    // }

    // //取消定时任务
    // function CancelTimingPush()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->CancelTimingPush("32");
    //     return ($ret);
    // }

    // // 设置标签
    // function BatchSetTag() {
    //     // 切记把这里的示例tag和示例token修改为你的真实tag和真实token
    //     $pairs = array();
    //     array_push($pairs, new TagTokenPair("tag1","token00000000000000000000000000000000001"));
    //     array_push($pairs, new TagTokenPair("tag1","token00000000000000000000000000000000001"));

    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->BatchSetTag($pairs);
    //     return $ret;
    // }

    // // 删除标签
    // function BatchDelTag() {
    //     // 切记把这里的示例tag和示例token修改为你的真实tag和真实token
    //     $pairs = array();
    //     array_push($pairs, new TagTokenPair("tag1","token00000000000000000000000000000000001"));
    //     array_push($pairs, new TagTokenPair("tag1","token00000000000000000000000000000000001"));

    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->BatchDelTag($pairs);
    //     return $ret;
    // }
        
    // //大批量下发给账号 android
    // //iOS 请构建MessageIOS 消息
    // function PushAccountListMultipleNotification()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $mess = new Message();
    //     $mess->setExpireTime(86400);
    //     $mess->setTitle('title');
    //     $mess->setContent('content');
    //     $mess->setType(Message::TYPE_NOTIFICATION);
    //     $ret = $push->CreateMultipush($mess, XingeApp::IOSENV_DEV);
    //     if (!($ret['ret_code'] === 0))
    //         return $ret;
    //     else
    //     {
    //         $result=array();
    //         $accountList1 = array('joelliu', 'joelliu2', 'joelliu3');
    //         array_push($result, $push->PushAccountListMultiple($ret['result']['push_id'], $accountList1));
    //         $accountList2 = array('joelliu4', 'joelliu5', 'joelliu6');
    //         array_push($result, $push->PushAccountListMultiple($ret['result']['push_id'], $accountList2));
    //         return ($result);
    //     }
    // }

    // //大批量下发给设备 android
    // //iOS 请构建MessageIOS 消息
    // function PushDeviceListMultipleNotification()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $mess = new Message();
    //     $mess->setExpireTime(86400);
    //     $mess->setTitle('title');
    //     $mess->setContent('content');
    //     $mess->setType(Message::TYPE_NOTIFICATION);
    //     $ret = $push->CreateMultipush($mess, XingeApp::IOSENV_DEV);
    //     if (!($ret['ret_code'] === 0))
    //         return $ret;
    //     else
    //     {
    //         $result=array();
    //         $deviceList1 = array('token1', 'token2', 'token3');
    //         array_push($result, $push->PushDeviceListMultiple($ret['result']['push_id'], $deviceList1));
    //         $deviceList2 = array('token4', 'token5', 'token6');
    //         array_push($result, $push->PushDeviceListMultiple($ret['result']['push_id'], $deviceList2));
    //         return ($result);
    //     }
    // }

    // //查询某个token的信息
    // function QueryInfoOfToken()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->QueryInfoOfToken("token");
    //     return ($ret);
    // }

    // //查询某个account绑定的token
    // function QueryTokensOfAccount()
    // {
    //     $push = new XingeApp($this->_access_id, $this->_secret_key);
    //     $ret = $push->QueryTokensOfAccount("nickName");
    //     return ($ret);
    // }   
    
}