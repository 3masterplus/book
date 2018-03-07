<?php defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/core/Api_Controller.php';

class Payment extends Api_Controller
{
    /**
     *
     *  构造函数
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('payment_lib');
        $this->load->library('credit_lib');
        $this->load->library('course_lib');
    }


    /**
     * 购买消费记录
     * @return json
     */
    public function history_get()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        $uid = $this->_guid;

        ParamErr('api/payment/history_get', 'get', 500);
        $limit  = api_get('limit') ? api_get('limit') : 10;
        $offset = api_get('offset') ? api_get('offset') : 0;
        
        $items = $this->course_lib->get_pay_history($uid, $limit, $offset);

        echoSucc('', $items);
    }


    /**
     * 获取一个订单的支付状态
     * @param  zhiler order_id
     * @return json
     */
    public function status_get($order_id)
    {
        $where = array(
            'transaction_key' => $order_id,
        );

        $status = $this->my_lib->get_a_value('transactions', 'status', $where);

        if (!$status) {
            echoErr('order no found', 'order no found');
        }

        $res = array(
            'status' => $status,
        );
        echoSucc('', $res);
    }

}
