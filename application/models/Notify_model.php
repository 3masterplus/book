<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
    
    class Notify_model extends MY_model
    {
        function __construct()
        {
            
        }

        /**
         * 获取消息列表
         * @param  array   $where
         * @param  integer $limit
         * @param  integer $offset  
         * @param  string  $orderby 
         * @param  string  $sort    
         * @param  boolean $forcount
         * @return array
         */
        function get_notify_list($select, $where, $limit = NULL, $offset = 0, $orderby = '', $sort = '', $forcount = false)
        {
            $this->db->select($select);
            $this->db->from('notify');
            $this->db->join('user_notify_relations', 'user_notify_relations.notify_id=notify.id');
            if ($where) {
                $this->db->where($where);
            }
            if ($orderby) {
                $this->db->order_by($orderby, $sort);
            }
            if ($limit) {
                $this->db->limit($limit, $offset);
            }
            if ($forcount) {
                return $this->db->count_all_results();
            }else {
                return $this->db->get()->result_array();
            }
        }

    }