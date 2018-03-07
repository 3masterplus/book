<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Payment extends Client_Controller
	{	
		function __construct()
		{
			parent::__construct();
			$this->load->library('payment_lib');
			$this->load->library('credit_lib');
			$this->load->library('form_validation');
		}
		
		function index()
		{
			$this->history();
		}
		
		//AJAX提交订单信息
		function ajax_create_a_transaction()
		{
			if(CI_POST('create_a_transaction'))
			{	
				$entity_unique_key = CI_POST('entity_unique_key');
				
				$this->form_validation->set_rules('entity_unique_key', 'entity_unique_key', 'required|callback_is_existent[entities.unique_key]');
				
				if($this->form_validation->run())
				{
					//获取当前用户的GUID
					$user_guid = $this->_guid;
					
					//首先获取被购买ENTITY的GUID
					$entity_guid = $this->my_lib->get_guid_by_unique_key($entity_unique_key);
					
					$condition = array('guid' => $entity_guid);
					
					//然后获取该ENTITY的类型
					$entity_subtype_id 	= $this->my_lib->get_a_value('entities', 'subtype_id', $condition);
					$entity_subtype		= $this->my_lib->get_subtype($entity_subtype_id);
					
					//获取用户要购买课程的原始价格
					if($entity_subtype == 'course')
					{
						$price = $this->my_lib->get_a_value('courses', 'by_course_fee', $condition);
					}
					elseif($entity_subtype == 'section')
					{
						$price = $this->my_lib->get_a_value('sections', 'price', $condition);
					}
					
					$title = $this->my_lib->get_a_value('entities', 'title', $condition);
					$transaction_key = $this->payment_lib->created_a_tranaction($user_guid, $entity_guid, $entity_subtype, $price, $price);
					
					$data = array(
						'title' 						=> $title,
						'original_price' 				=> $price,
						'transaction_key' 				=> $transaction_key,
						'total_of_available_credits' 	=> $this->credit_lib->calculate_a_user_total_credit($user_guid)
					);
					
					$this->ajax_ini($data);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//用户在购买课程时消费积分
		function ajax_consume_credits_with_transaction()
		{
			if(CI_POST('consume_credits_with_transaction'))
			{
				$transaction_key = CI_POST('transaction_key');
				$credit_consumed = CI_POST('credits_consumed');
				
				$this->form_validation->set_rules('transaction_key', 'transaction_key', 'required|callback_is_existent[transactions.transaction_key]');
				
				if($this->form_validation->run())
				{
					$user_guid = $this->_guid;
					$credit_consumed = abs((int)$credit_consumed);
					$total_credits = $this->credit_lib->calculate_a_user_total_credit($user_guid);
					
					if($credit_consumed <= $total_credits)
					{
						$fields = array('original_price', 'credit_transaction_id');
						$transaction = $this->payment_lib->get_a_transaction_info($transaction_key, $fields);
					
						$original_price = $transaction['original_price'];
						$credit_transaction_id = $transaction['credit_transaction_id'];
						
						//新的价格 ＝ 产品原价 - 重新要消费的积分 x 积分和货币的兑换率
						$new_amount = $original_price - credit_to_currency_conversion($credit_consumed);
						
						if($new_amount < 0)
						{
							$new_amount = 0;
							$credit_consumed = $original_price * 10;
						}
						
						if($credit_transaction_id > 0)
						{
							$this->credit_lib->update_a_credit_transaction_status($credit_transaction_id, strtoupper('cancelled'));
							
							$credit_consumed = 0 - $credit_consumed;
							$credit_transaction_id = $this->credit_lib->create_a_credit_transaction($user_guid, $credit_consumed, 'pay', '', '', strtoupper('suspended'));
							$this->my_lib->update_records('transactions', array('credit_transaction_id' => $credit_transaction_id), array('transaction_key' => $transaction_key));
						}
						else
						{
							$credit_consumed = 0 - $credit_consumed;
							$credit_transaction_id = $this->credit_lib->create_a_credit_transaction($user_guid, $credit_consumed, 'pay', '', '', strtoupper('suspended'));
							$this->my_lib->update_records('transactions', array('credit_transaction_id' => $credit_transaction_id), array('transaction_key' => $transaction_key));
						}
					
						$this->payment_lib->update_a_transaction($transaction_key, $new_amount, $credit_transaction_id);
						
						$data = array(
							'currency_saved' 	=> credit_to_currency_conversion(abs($credit_consumed)),
							'remaining_credits'	=> $total_credits - abs($credit_consumed),
							'new_amount' 		=> $new_amount
						);
						
						$this->ajax_ini($data);
					}
					else
					{
						$this->_ajax_message = '消费的积分不能超过您的积分总额';
					}
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
				
			}
		}
		
		
		//ajax取消一个订单
		function ajax_cancel_a_transaction()
		{
			if(CI_POST('cancel_a_transaction'))
			{
				$transaction_key = CI_POST('transaction_key');
				$this->form_validation->set_rules('transaction_key', 'transaction_key', 'required|callback_is_existent[transactions.transaction_key]');
				
				if($this->form_validation->run())
				{
					$this->payment_lib->cancel_a_transaction($transaction_key);
					
					$credit_transaction_id = $this->my_lib->get_a_value('transactions', 'credit_transaction_id', array('transaction_key' => $transaction_key));
					
					if($credit_transaction_id > 0)
					{
						$this->credit_lib->update_a_credit_transaction_status($credit_transaction_id, strtoupper('cancelled'));
					}
					
					$this->ajax_ini();
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//ajax取消积分参与支付
		function ajax_cancel_credits_consumption()
		{
			if(CI_POST('cancel_credits_consumption'))
			{
				$transaction_key = CI_POST('transaction_key');
				$this->form_validation->set_rules('transaction_key', 'transaction_key', 'required|callback_is_existent[transactions.transaction_key]');
				
				if($this->form_validation->run())
				{
					$user_guid = $this->_guid;
					
					$fields = array('original_price', 'credit_transaction_id');
					$transaction = $this->payment_lib->get_a_transaction_info($transaction_key, $fields);
					
					$original_price 		= $transaction['original_price'];
					$credit_transaction_id 	= $transaction['credit_transaction_id'];
					
					if($credit_transaction_id != '')
					{
						$this->credit_lib->update_a_credit_transaction_status($credit_transaction_id, strtoupper('cancelled'));
					}
					
					//清空“transactions”表中的“credit_transaction_id”
					$condition = array('transaction_key' => $transaction_key);
					$data = array('credit_transaction_id' => 0, 'amount' => $original_price);
					$this->my_lib->update_records('transactions', $data, $condition);
					
					$data = array('new_amount' => $original_price);
					$this->ajax_ini($data);
					
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//ajax获取一个transaction的使用积分的情况
		function ajax_check_a_credit_consumption()
		{
			if(CI_POST('check_a_credit_consumption'))
			{
				$transaction_key = CI_POST('transaction_key');
				$this->form_validation->set_rules('transaction_key', 'transaction_key', 'required|callback_is_existent[transactions.transaction_key]');
				
				if($this->form_validation->run())
				{
					//获取伴随transaction的那个积分交易值
					$credit_transaction_id = $this->my_lib->get_a_value('transactions', 'credit_transaction_id', array('transaction_key' => $transaction_key));
					
					if($credit_transaction_id > 0)
					{
						$condition = array('id' => $credit_transaction_id, 'status' => strtoupper('suspended'));
						$credit_consumed = $this->my_lib->get_a_value('credits', 'amount', $condition);
						$this->credit_lib->update_a_credit_transaction_status($credit_transaction_id, strtoupper('cancelled'));
					}
					else
					{
						$credit_consumed = 0;
					}
					
					$total_credits = $this->credit_lib->calculate_a_user_total_credit($this->_guid);
					
					$data = array('credit_consumed' => abs($credit_consumed), 'total_credits' => $total_credits);
					$this->ajax_ini($data);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//判断一个订单是否完成
		function ajax_check_a_transaction()
		{
			if(CI_POST('check_a_transaction'))
			{
				$transaction_key = CI_POST('transaction_key');
				$this->form_validation->set_rules('transaction_key', 'transaction_key', 'required|callback_is_existent[transactions.transaction_key]');
				
				if($this->form_validation->run())
				{
					$condition = array('transaction_key' => $transaction_key, 'status' => strtoupper('completed'));
					$if_completed = $this->my_lib->check_a_record('transactions', $condition);
					$this->ajax_ini($if_completed);
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//ajax获取一个订单的信息
		function ajax_get_a_transaction()
		{
			if(CI_POST('get_a_transaction'))
			{
				$transaction_key = CI_POST('transaction_key');
				$this->form_validation->set_rules('transaction_key', 'transaction_key', 'required|callback_is_existent[transactions.transaction_key]');
				
				if($this->form_validation->run())
				{
					$fields = array('amount', 'original_price', 'entity_guid', 'credit_transaction_id');
					$transaction = $this->payment_lib->get_a_transaction_info($transaction_key, $fields);
					
					$entity_guid 	= $transaction['entity_guid'];
					$price			= $transaction['amount'];
					$original_price	= $transaction['original_price'];
					
					$title = $this->my_lib->get_a_value('entities', 'title', array('guid' => $entity_guid));
					
					$credit_transaction_id	= $transaction['credit_transaction_id'];
					
					if($credit_transaction_id > 0)
					{
						$credit_consumed = $this->my_lib->get_a_value('credits', 'amount', array('id' => $credit_transaction_id));
					}
					else
					{
						$credit_consumed = 0;
					}
					
					$total_credits = $this->credit_lib->calculate_a_user_total_credit($this->_guid);
					
					$data = array(
						'title' 						=> $title,
						'price' 						=> $price,
						'original_price' 				=> $original_price,
						'total_of_available_credits' 	=> $total_credits,
						'currency_saved' 				=> credit_to_currency_conversion(abs($credit_consumed)),
						'credit_consumed' 				=> abs($credit_consumed)
					);
					
					$this->ajax_ini($data); 
				}
				else
				{
					$this->_ajax_message = $this->my_lib->generate_error_message();
				}
				
				$this->ajax_response();
			}
		}
		
		//temporary test
		function pay()
		{
			$this->load->library('course_lib');
			$transaction_key = '1448344062z3hdlU';
			$user_guid = $this->_guid;
			$transaction = $this->payment_lib->get_a_transaction_info($transaction_key);
			$entity_guid = $transaction['entity_guid'];
			$entity_subtype = $transaction['entity_subtype'];
			$credit_transaction_id = $transaction['credit_transaction_id'];
			$transaction_data = $transaction;
			
			$this->payment_lib->complete_a_payment($transaction_key, $user_guid, $entity_guid, $entity_subtype, $transaction_data, $credit_transaction_id);
		}
		
		//获取消息列表
		function history()
		{
			$user_guid = $this->_guid;
			
			$data['history'] = $this->payment_lib->get_payment_history($user_guid);
			
			
			$this->show_global_msg('session');
			$this->template->set('page','account-page');
			$this->template->set_partial('navigation','partials/new_vertical_navigation', array('highlight' => ''));
			
			$action = '购买记录';
			$arr = array('header_type' => 'header', 'text' => $action);
			$this->template->set_partial('header','partials/new_horizental_navigation', $arr);
			$this->template->set_partial('sidebar', 'global/user_center_sidebar', array('current' => $action));
			
			$this->template->set_layout('new_layout_3');
			$this->template->build('payment/history', $data);
		}
	}