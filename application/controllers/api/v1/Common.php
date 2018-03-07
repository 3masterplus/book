<?php defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/core/Api_Controller.php';

class Common extends Api_Controller
{
    /**
     *
     *  构造函数
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('api/api_common_lib');
        $this->load->library('xinge_lib' );
    }


    /**
     * 记录设备的基础信息，比如推送令牌
     * @return json
     */
    public function device_post()
    {
        $pdata = array();
        ParamErr('api/common/device_post', 'post', 500);
        $device_token = api_post('device_token');
        $pdata['device_token'] = $device_token;
        if ($device_token) {
            $this->api_common_lib->update_device_data($pdata);
        }
        
        // $this->xinge_lib->PushSingleDeviceDispatcher('9fc58efd0e79e3aed8636b8b5e0bf644ec400a53219bc3e3c6604cff749ee692', 'test title', 'test content');

        // $this->xinge_lib->PushAllDevicesDispatcher('test title to all devices', 'test content to all all devices');


        echoSucc();
    }
}
