<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Api_common_lib extends my_lib
{
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    /**
     * 更新用户的设备信息
     * @param  array $pdata
     * @return bool
     */
    public function update_device_data($pdata)
    {
        if (!$pdata) {
            return true;
        }
        /**
         * 记录推送令牌
         */
        if (isset($pdata['device_token'])) {
            $device_token = $pdata['device_token'];
            //查看该token是否已经记录了
            $where = array(
                'device_token' => $device_token,
                'type' => $this->ci->_device,
            );
            //如果没有记录则新增
            $isRecord = $this->ci->my_lib->check_a_record('device_tokens', $where);
            if (!$isRecord) {
                $device_token_data = array(
                    'device_token' => $device_token,
                    'type' => $this->ci->_device,
                    'time_created' => $this->ci->_t
                );
                $this->ci->my_lib->create_a_record('device_tokens', $device_token_data);
            }
            //如果用户登陆了的话，则绑定用户guid和当前设备关系
            if ($this->ci->_guid) {
                $isRecord = $this->ci->my_lib->check_a_record('device_token_relations', $where);
                if (!$isRecord){
                    $device_token_relation_data = array(
                        'device_token' => $device_token,
                        'type' => $this->ci->_device,
                        'user_guid' => $this->ci->_guid,
                        'time_created' => $this->ci->_t
                    );
                    $this->ci->my_lib->create_a_record('device_token_relations', $device_token_relation_data);        
                }
                else{
                    $this->ci->my_lib->update_records('device_token_relations', array('user_guid' => $uid), $where);
                }
            }
        }
        return true;
    }
}
