<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class River_lib extends my_lib
	{	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
		}
		
		/**
			这是一个给用户积分的方法。如果该方法被调用的时候
		*/
		function grant_credit($user_guid, $action, $river_id, $amount = 0){
			$action_id = $this->get_action_id($action); //获取这个"Action"对应的ID
			$data = array('user_guid' => $user_guid, 'time_created' => time(), 'river_id' => $river_id, 'action_id' => $action_id);
			$data['amount'] = iif($amount == 0, $this->get_a_value('actions', 'credit', array('id' => $action_id)), $amount);
			
			RETURN $this->create_a_record('credits', $data);
		}
		
		//给用户颁发勋章
		function award_badge($user_guid, $badge)
		{
			$badge_id = $this->get_a_value('badges', 'id', array('badge' => $badge));
			$condition = array('user_guid' => $guid, 'badge_id' => $badge_id);
			
			if(!$this->check_a_value('user_badge_relations', $condition)){
				$data = array('user_guid' => $user_guid, 'badge_id' => $badge_id);
				$this->create_a_record('user_badge_relations', $data);	
				RETURN True;
			}
			
			RETURN False;
		}
		
		//记录用户行为
		function logit($user_guid, $action, $related_guids = array(), $data = ''){
			//拼接写入到“River”表中的数据
			$data = array(
				'action_id'			=> $this->get_action_id($action),
				'time_created'		=> time(),
				'user_guid'			=> $user_guid,
				'user_agent'		=> $this->_user_agent,
				'platform'			=> $this->_platform,
				'device'			=> $this->_device,
				'is_mobile_browser'	=> $this->_is_mobile_browser,
				'is_wechat_browser'	=> $this->_is_wechat_browser,
				'data'				=> iif($data == '', $data, serialize($data))
			);
			
			//计算出“related_guid”的数量
			$number_of_related_guids = count($related_guids);
			
			//如果“related_guids”所包含的数组大于或等于一条记录，拼接出“related_guid_1”样式的数据
			if($number_of_related_guids > 0){
				for($i = 0; $i < $number_of_related_guids; $i++){
					$data["related_guid_".($i+1)] = $related_guids[$i];
				}
			}
			
			return $this->create_a_record('river', $data);
		}
		
		//根据“action_id”获取“action”
		private function get_action($action_id){
			RETURN $this->get_a_value('actions', 'action', array('id' => $action_id));	
		}
		
		// 根据“action”获取“action_id”
		private function get_action_id($action){
			RETURN $this->get_a_value('actions', 'id', array('action' => $action));
		}
	}