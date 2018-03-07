<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

	class MY_Model extends CI_Model
	{
		//插入数据，返回新建的被插入数据的ID. 如果没有自增ID, 返回为0.
		function insert_a_row($table, $data)
		{
			$this->db->insert($table, $data);
			return $this->db->insert_id();
		}
		
		//更新数据，输入数据库名称，更新的数据，条件. 如果更新成功，返回TRUE,  否则，返回FALSE.
		function update_rows($table, $data, $condition)
		{
			return $this->db->update($table, $data, $condition);
		}
		
		//从数据库中读取一条或多条数据的标准方法
		function select_rows($table, $field, $condition, $limit, $offset, $orderby, $sort, $forcount)
		{
			$this->db->select($field, FALSE);
			if (!is_null($condition)) $this->db->where($condition);
			if (!is_null($orderby)) $this->db->order_by($orderby, $sort);
			if ($forcount) return $this->db->count_all_results($table);
			else return $this->db->get($table, $limit, $offset);
		}
				
		//直接通过SQL语句来查询
		function select_rows_with_sql($sql, $limit = NULL, $offset = 0, $orderby = NULL, $sort = 'DESC')
		{
			if(!is_null($orderby)) $sql .= "ORDER BY $orderby $sort ";
			if(!is_null($limit)) $sql .= "LIMIT $limit offset $offset ";
			return $this->db->query($sql);
		}
		
		//删除数据，输入要删除的数据库名称，条件. 如果删除成功，返回true.
		function delete_rows($table, $condition)
		{
			return $this->db->delete($table, $condition);
		}
	}