<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Count_lib extends my_lib
	{	
		function __construct()
		{
			parent::__construct();
			$this->ci = & get_instance();
		}
		
		//count++
		function plus($guid, $type, $plus = 1)
		{		
			//如果数据库存在该记录，读数+1，否则创建一条这样的记录	
			if($this->check_a_record('counts', array('guid' => $guid, 'type' => $type)))
			{
				$sql = "UPDATE counts SET count = count + $plus where guid = '$guid' AND type = '$type' ";
				return $this->ci->db->query($sql);
			}
			else
			{
				$data = array('guid' => $guid, 'type' => $type, 'count' => $plus);
				return $this->create_a_record('counts', $data);
			}
		}
		
		//count--
		function minus($guid, $type, $minus = 1)
		{
			$sql = "UPDATE counts SET count = count - $minus where guid = '$guid' AND type = '$type' ";
			return $this->ci->db->query($sql);
		}
		
		//获取读数
		public function get_count($guid, $type){
		
			$condition = array('guid' => $guid, 'type' => $type);
			
			if(!$this->check_a_record('counts', $condition)){
				return 0;
			} else {
				return $this->get_a_value('counts', 'count', $condition);
			}
		}
		
		//直接更新一个“count”的数，如果这个“count”数还不存在，创建一个新记录
		function update_count($guid, $type, $count)
		{
			//如果数据库存在该记录，读数更新，否则创建一条这样的记录	
			
			$condition = array('guid' => $guid, 'type' => $type);
			
			if($this->check_a_record('counts', $condition))
			{
				$data = array('count' => $count);
				return $this->update_records('counts', $data, $condition);
			}
			else
			{
				$data = array('guid' => $guid, 'type' => $type, 'count' => $count);
				return $this->create_a_record('counts', $data);
			}
		}
		
	}