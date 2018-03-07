<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
	
	require_once(APPPATH.'/third_party//pingpp-php/init.php');
	require_once(__DIR__.'/Payment.php');

	class Pay extends Payment
	{   
    	private $gateway = NULL;
    	
    	function __construct(){
    		parent::__construct();
    		// \Pingpp\Pingpp::setApiKey('sk_test_nHOS0KH8CCmDyTGq5KXnvXXL');
    		\Pingpp\Pingpp::setApiKey('sk_live_nr9m5S0afHa5f1SCOOenLOu5');
    	}


		/**
		 * 收到一个支付请求，将该请求发送到ping++获取支付凭据，并返回该支付凭据给前端
		 * @return 
		*/
		
		function order(){
			
			$transaction_key = CI_POST('transaction_key');
			$gateway = CI_POST('gateway');
            $extra = CI_POST('extra');
			$this->gateway = $gateway;
			$this->form_validation->set_rules('transaction_key', 'transaction_key', 'trim|required|callback_is_existent[transactions.transaction_key]');
			$this->form_validation->set_rules('gateway', 'gateway', 'trim|required');
			
			if($this->form_validation->run()){
				//查看该订单状态
				$condition = array('transaction_key' => $transaction_key);
				$order_data = $this->my_lib->get_records('transactions', '*', $condition, 1, 0);
				$order_data   = $order_data[0];
				$order_status = $order_data['status'];
				$entity_title = $this->my_lib->get_a_value('entities', 'title', array('guid' => $order_data['entity_guid'])  );
				
				//根据不同的订单状态处理订单
				switch ($order_status) {
					
					case 'PROCESSING'://待支付
						$can_pay = true;
						break;
					
					case 'COMPLETED': //已支付
						$can_pay = false;
						$this->_ajax_message = '该订单已经支付成功了，请勿重复支付！';
						break;
					
					case 'CANCELLED': //已取消支付
						$can_pay = false;
						$this->_ajax_message = '该订单已经被取消了，请尝试刷新页面后再次支付！';
						break;
					
					case 'REFUNDED': //已退款
						$can_pay = false;
						$this->_ajax_message = '该订单是已退款，请勿支付！';
						break;
					
					default:
						$can_pay = false;
						$this->_ajax_message = '未知状态的订单';
						break;
				}
					
				//合法的支付请求,换取ping++支付凭证
				
				if ($can_pay) {
				
					//更新支付渠道
					$this->my_lib->update_records('transactions', array('gateway' => $gateway), $condition  );
					
					try {
						//支付
						$ch = \Pingpp\Charge::create(
							array(
								'order_no'  => $transaction_key,
								'app'       => array('id' => $this->get_pingpp_app()), //目前就一个网页支付，后期这里可以根据device自动切换其他的app_id
								'channel'   => strtolower($gateway),
								'amount'    => $order_data['amount']*100,
								'client_ip' => $this->_ip_address == '::1' ? '127.0.0.1' : $this->_ip_address,
								'currency'  => 'cny', //目前只支持人民币
								'subject'   => $entity_title,
								'body'      => $entity_title,
								'extra'     => $this->get_extra_by_channel($extra),
							)
						);
						
						$ch = json_decode($ch);
						
						// 记录支付凭证ID
						$this->my_lib->update_records('transactions', array('charge_id' => $ch->id ), $condition  );
						$this->ajax_ini($ch);
					}catch (Exception $e) {
						//该处应该给一个友好的错误给用户，而把ping++返回的错误写入日志中，暂时直接返回ping++的错误给了用户。
						$this->_ajax_message = $e->getMessage();
					}
				}
			}else{
				$ch = $this->my_lib->generate_error_message();
			}
			
			$this->ajax_response();
		}


		/**
		 * 获取pingpp的应用ID
		 * @return [type] [description]
		*/
		
		private function get_pingpp_app(){
			// 目前pingpp中就一个网页应用
			return 'app_fLGi1O0erPWTiT4K';
		}

		/**
		 * 根据不同的支付渠道返回额外的渠道参数
		 * @return
		*/
		
		private function get_extra_by_channel($extra){
            
            if ($extra) {
                $res = @json_decode($extra);
                if ($res) {
                    return $res;
                }
            }
            return array();
        }



		/**
		 * 接收ping++的支付结果通知
		 * @return boolean
		*/
		
		function webhooks_res(){
			$event = json_decode(file_get_contents("php://input"));
			
			//test
			
			/**
			$event_charge = '{
				"id": "ch_Hm5uTSifDOuTy9iLeLPSurrD",
				"object": "charge",
				"created": 1410778843,
				"livemode": true,
				"paid": true,
				"refunded": false,
				"app": "app_1Gqj58ynP0mHeX1q",
				"channel": "upacp",
				"order_no": "123456789",
				"client_ip": "127.0.0.1",
				"amount": 100,
				"amount_settle": 0,
				"currency": "cny",
				"subject": "Your Subject",
				"body": "Your Body",
				"extra":{},
				"time_paid": null,
				"time_expire": 1410782443,
				"time_settle": null,
				"transaction_no": null,
				"refunds": {
					"object": "list",
					"url": "/v1/charges/ch_Hm5uTSifDOuTy9iLeLPSurrD/refunds",
					"has_more": false,
					"data": []
				},
				"amount_refunded": 0,
				"failure_code": null,
				"failure_msg": null,
				"credential": {
					"object": "credential",
					"upacp": {
						"tn": "201409151900430000000",
						"mode": "01"
					}
				},
				"description": null
			}';
			
			$event = '{
				"id": "evt_la06CoQAiPojSgJKe5gt3nwq",
				"created": 1427555016,
				"livemode": false,
				"type": "charge.succeeded",
				"data": {
					"object": '.$event_charge.'
				},
				"object": "event",
				"pending_webhooks": 0,
				"request": null
			}';
			
			$event = json_decode($event);
			
			//可选项，验证 webhooks 签名
			
			//POST 原始请求数据是待验签数据，请根据实际情况获取
			$raw_data = file_get_contents('php://input');
			$raw_data = '{"id":"evt_eYa58Wd44Glerl8AgfYfd1sL","created":1434368075,"livemode":true,"type":"charge.succeeded","data":{"object":{"id":"ch_bq9IHKnn6GnLzsS0swOujr4x","object":"charge","created":1434368069,"livemode":true,"paid":true,"refunded":false,"app":"app_vcPcqDeS88ixrPlu","channel":"wx","order_no":"2015d019f7cf6c0d","client_ip":"140.227.22.72","amount":100,"amount_settle":0,"currency":"cny","subject":"An Apple","body":"A Big Red Apple","extra":{},"time_paid":1434368074,"time_expire":1434455469,"time_settle":null,"transaction_no":"1014400031201506150354653857","refunds":{"object":"list","url":"/v1/charges/ch_bq9IHKnn6GnLzsS0swOujr4x/refunds","has_more":false,"data":[]},"amount_refunded":0,"failure_code":null,"failure_msg":null,"metadata":{},"credential":{},"description":null}},"object":"event","pending_webhooks":0,"request":"iar_Xc2SGjrbdmT0eeKWeCsvLhbL"}';
			
			// 签名在头部信息的 x-pingplusplus-signature 字段
			$signature = 'BX5sToHUzPSJvAfXqhtJicsuPjt3yvq804PguzLnMruCSvZ4C7xYS4trdg1blJPh26eeK/P2QfCCHpWKedsRS3bPKkjAvugnMKs+3Zs1k+PshAiZsET4sWPGNnf1E89Kh7/2XMa1mgbXtHt7zPNC4kamTqUL/QmEVI8LJNq7C9P3LR03kK2szJDhPzkWPgRyY2YpD2eq1aCJm0bkX9mBWTZdSYFhKt3vuM1Qjp5PWXk0tN5h9dNFqpisihK7XboB81poER2SmnZ8PIslzWu2iULM7VWxmEDA70JKBJFweqLCFBHRszA8Nt3AXF0z5qe61oH1oSUmtPwNhdQQ2G5X3g==';
        	
        	// 请从 https://dashboard.pingxx.com 获取「Webhooks 验证 Ping++ 公钥」
        	$pub_key_path = __DIR__ . "/rsa_public_key.pem";
        	
        	$result = verify_signature($raw_data, $signature, $pub_key_path);
        	
        	if ($result !== 1) {
        		echo 'verification succeeded';
        		exit;
        	}
        	
        	*/


			// 对异步通知做处理
			if (!isset($event->type)) {
				header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
				exit("fail");
			}
			
			switch ($event->type) {
				case "charge.succeeded":
				
					// 开发者在此处加入对支付异步通知的处理代码
					$order_no = $event->data->object->order_no;
					$paid_st = $event->data->object->paid;
					
					if ($paid_st) {
						//加载课程的函数库
						$this->load->library('course_lib');
						
						// 更新了订单状态
						$this->my_lib->update_records('transactions', array('status' => 'COMPLETED'), array('transaction_key' => $order_no));
						
						// 首先，获取订单相关信息
						$fields = array('user_guid', 'entity_subtype', 'entity_guid', 'amount');
						$condition = array('transaction_key' => $order_no);
						$transaction = $this->my_lib->get_records('transactions', $fields, $condition);
						
						$user_guid = $transaction[0]['user_guid'];
						$entity_subtype = $transaction[0]['entity_subtype'];
						$entity_guid = $transaction[0]['entity_guid'];
						$amount_paid = $transaction[0]['amount'];
						
						if($entity_subtype == 'COURSE'){
						
							//建立用户和所购买课程的关系
							$this->course_lib->pay_a_course($user_guid, $entity_guid); //支付了课程
							$this->course_lib->join_a_course($user_guid, $entity_guid); //加入了课程
							$this->course_lib->follow_a_course($user_guid, $entity_guid); //关注了课程
							
							//讲这位用户的购买行为写入RIVER
							$related_guids = array($entity_guid);
							$data = array('order_no' => $order_no, 'amount' => $amount_paid);
							$river_id = $this->river_lib->logit($user_guid, 'pay_a_course', $related_guids, $data);
							
							//给这个用户相应的积分
							$this->river_lib->grant_credit($user_guid, 'pay_a_course', $river_id);
							
						}elseif($entity_subtype == 'SECTION'){
							$course_guid = $this->my_lib->get_a_value('sections', 'course_guid', array('guid' => $entity_guid));
							$this->course_lib->pay_a_section($user_guid, $course_guid, $entity_guid); //支付了课程
							$this->course_lib->join_a_course($user_guid, $course_guid); //加入了课程
							$this->course_lib->follow_a_course($user_guid, $course_guid); //关注了课程
							
							//讲这位用户的购买行为写入RIVER
							$related_guids = array($entity_guid, $course_guid);
							$data = array('order_no' => $order_no, 'amount' => $amount_paid);
							$river_id = $this->river_lib->logit($user_guid, 'pay_a_section', $related_guids, $data);
							
							//给这个用户相应的积分
							$this->river_lib->grant_credit($user_guid, 'pay_a_section', $river_id);
						}						
					} else {
						$this->my_lib->update_records('transactions', array('status' => 'FAILED'), array('transaction_key' => $order_no) );
						}
						
					header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
					break;
					
				case "refund.succeeded":
					// 开发者在此处加入对退款异步通知的处理代码
					header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
					break;
					
				default:
					header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
					break;
			}
		}
		
		private function verify_signature($raw_data, $signature, $pub_key_path) {
			$pub_key_contents = file_get_contents($pub_key_path);
			// php 5.4.8 以上，第四个参数可用常量 OPENSSL_ALGO_SHA256
			return openssl_verify($raw_data, base64_decode($signature), $pub_key_contents, 'sha256');
		}
	}