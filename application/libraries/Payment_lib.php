<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Payment_lib extends my_lib
	{	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
		}

		/*
			当用户成功完成了一笔支付交易，controller中的方法需要向library的方法传入付款信息，
			包括：entity_guid, entity_subtype, original_price, amount
		*/
		
		function complete_a_payment($transaction_key, $user_guid, $entity_guid, $entity_subtype, $transaction_data, $credit_transaction_id = 0)
		{
			$this->ci->db->trans_start();
		
			//首先，将订单的状态转成“completed”
			$this->update_a_transaction_status($transaction_key, strtoupper('completed'));
			
			//然后，将锁定的积分解锁
			if($credit_transaction_id > 0)
			{
				$this->ci->credit_lib->update_a_credit_transaction_status($credit_transaction_id, strtoupper('completed'), $transaction_data);
			}
			
			//然后，为用户开通各种服务
			if($entity_subtype == 'COURSE')
			{
				//为用户开通相应的服务
				$this->ci->course_lib->pay_a_course($entity_guid, $user_guid);
				$this->ci->course_lib->join_a_course($entity_guid, $user_guid);
				$this->ci->course_lib->follow_a_course($entity_guid, $user_guid);
			}
			elseif($entity_subtype == 'SECTION')
			{
				$course_guid = $this->get_a_value('sections', 'course_guid', array('guid' => $entity_guid));
				$this->ci->course_lib->pay_a_section($course_guid, $entity_guid, $user_guid);
				$this->ci->course_lib->follow_a_course($course_guid, $user_guid);
			}
			
			//将用户的购买行为写入River
			$related_guids = array($entity_guid);
			$river_id = $this->ci->river_lib->logit($user_guid, 'buy', $related_guids, $transaction_data);
			
			$this->ci->credit_lib->update_a_credit_transaction_status($credit_transaction_id, strtoupper('completed'), $river_id, $transaction_data);
			
			$this->ci->db->trans_complete();
			
			return $this->ci->db->trans_status();
		}
		
		
		//创建一个交易记录
		function created_a_tranaction($user_guid, $entity_guid, $entity_subtype, $price)
		{
			$data = array(
				'transaction_key'		=> $this->get_a_transaction_key(),
				'user_guid'				=> $user_guid,
				'time_created'			=> time(),
				'entity_subtype' 		=> $entity_subtype,
				'entity_guid' 			=> $entity_guid,
				'original_price'		=> $price,
				'amount'				=> $price,
				'status'				=> strtoupper('processing'),
			);
			
			$this->create_a_record('transactions', $data);
			return $data['transaction_key'];
		}
		
		//取消一个订单
		function cancel_a_transaction($transaction_key)
		{
			//首先，先把订单的状态从“processing”变成“cancelled”
			$condition = array('transaction_key' => $transaction_key);
			$data = array('status' => strtoupper('cancelled'));
			return $this->update_records('transactions', $data, $condition);
		}
		
		//更新一个订单
		function update_a_transaction($transaction_key, $new_amount, $credit_transaction_id)
		{
			$condition = array('transaction_key' => $transaction_key);
			$data = array('amount' => $new_amount, 'credit_transaction_id' => $credit_transaction_id);
			return $this->update_records('transactions', $data, $condition);
		}
		
		//更新订单的状态
		function update_a_transaction_status($transaction_key, $new_status)
		{
			$condition = array('transaction_key' => $transaction_key);
			$data = array('status' => $new_status);
			return $this->update_records('transactions', $data, $condition);
		}
		
		//获取一个“transaction”的全部信息
		function get_a_transaction_info($transaction_key, $fields = array('*'))
		{
			$condition = array('transaction_key' => $transaction_key);
			$transaction = $this->get_records('transactions', $fields, $condition);
			return $transaction[0];
		}
		
		//随机生成一个交易的KEY
		private function get_a_transaction_key()
		{
			return time().rand_str(6);
		}
		
		//获取一个用户的购买历史
		function get_payment_history($user_guid)
		{
			$condition = array('user_guid' => $user_guid, 'status' => strtoupper('completed'));
			$fields = array('transaction_key', 'time_created', 'entity_subtype', 'entity_guid', 'amount');
			return $this->get_records('transactions', $fields, $condition, null, 0, 'time_created', 'DESC');
		}
		
	}