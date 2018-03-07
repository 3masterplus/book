<?php defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/core/Api_Controller.php';

class Notify extends Api_Controller
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
        $this->load->library('notify_lib' );
    }

    /**
     * 获取我的通知列表
     * @return json
     */
    public function list_get()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();

        ParamErr('api/notify/list_get', 'get', 500);
        $limit  = api_get('limit') ? api_get('limit') : 10;
        $offset = api_get('offset') ? api_get('offset') : 0; 
        $type   = api_get('type');

        $select_items = 'notify.*,user_notify_relations.user_guid,user_notify_relations.open_time,user_notify_relations.is_read';
        $where = array(
            'user_notify_relations.user_guid' => $this->_guid,
        );
        if ($type) {
            $where['notify.type'] = $type;
        }
        $items = $this->notify_lib->get_notify_list($select_items, $where, $limit, $offset, 'user_notify_relations.time_created', 'desc');
        $this->notify_lib->handle_notify_data($items);
        $count = $this->notify_lib->get_notify_list('notify.id', $where, NULL, 0, NULL, NULL, true);

        $cursor = array(
            'total'  => $count,
            'limit'  => $limit,
            'offset' => $offset,
        );
        echoSucc('', $items, $cursor);
    }

    /**
     * 标记消息体
     * @return json
     */
    public function mark_post()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        ParamErr('api/notify/mark_post', 'post', 500);
        $notify_id = api_get('notify_id');
        try {
            $this->notify_lib->mark_read($this->_guid, $notify_id);   
        } catch (Exception $e) {
            echoErr($e->getMessage(), $e->getDescription(), $e->getHttpCode() );
        }
        echoSucc();
    }

}