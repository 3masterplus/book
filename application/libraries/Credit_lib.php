<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Credit_lib extends my_lib
	{	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
		}
		
		//创建一个积分交易记录
		function create_a_credit_transaction($user_guid, $amount, $action = '', $river_id = '', $data = '', $status = 'COMPLETED')
		{
			$action_id = iif($action != '', $this->get_action_id($action), '');
			$data = iif($data != '', serialize($data), '');
			
			$transaction = array(
				'user_guid' 	=> $user_guid,
				'time_created' 	=> time(),
				'amount' 		=> $amount,
				'action_id' 	=> $action,
				'river_id' 		=> $river_id,
				'data'			=> $data,
				'status'		=> $status
			);
			
			return $this->create_a_record('credits', $transaction);
		}
		
		//改变一个积分交易记录的状态
		function update_a_credit_transaction_status($credit_transaction_id, $new_status, $river_id = 0, $data = '')
		{
			$condition = array('id' => $credit_transaction_id);
			
			$data = array('status' => $new_status);
			if($data != '') $data['data'] = serialize($data);
			
			return $this->update_records('credits', $data, $condition);
		}
		
		//计算一个用户的积分总值
		function calculate_a_user_total_credit($user_guid)
		{
			$sql  = "select sum(amount) as total from credits where user_guid = $user_guid AND status != 'CANCELLED'";
			$result = $this->get_records_with_sql($sql);
			$total = $result[0]['total'];
			
			if($total != 'null') return $total;
			return 0;
		}
		
		
		//根据“action_id”获取“action”
		private function get_action($action_id)
		{
			RETURN $this->get_a_value('actions', 'action', array('id' => $action_id));	
		}
		
		// 根据“action”获取“action_id”
		private function get_action_id($action)
		{
			RETURN $this->get_a_value('actions', 'id', array('action' => $action));
		}
	}