<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
    
class Api_course_model extends MY_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取我加入的课程列表
     * @param  string  $select_items [description]
     * @param  array   $where        [description]
     * @param  integer $limit        [description]
     * @param  integer $offset       [description]
     * @param  string  $orderby      [description]
     * @param  string  $sort         [description]
     * @param  boolean $forcount     [description]
     * @return array                 [description]
     */
    public function get_my_courses($select_items = NULL, $where = NULL, $limit = 10, $offset = 0, $orderby = NULL, $sort = NULL, $forcount = false)
    {
        $this->db->select($select_items);
        $this->db->from('courses');
        $this->db->join('user_course_relations', "user_course_relations.course_guid=courses.guid and user_course_relations.action= 'join_a_course' ");
        if ($where) {
            $this->db->where($where);
        }
        if ($orderby) {
            $this->db->orderby($orderby, $sort);
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

    /**
     * 获取courses数据，join entities
     * @param  integer $select_items
     * @param  integer $where
     * @param  integer $limit
     * @param  integer $offset
     * @param  string  $orderby
     * @param  string  $sort
     * @param  boolean $forcount
     * @return json
     */
    public function get_courses_with_entities($select_items, $where, $limit = NULL, $offset = 0, $orderby = NULL, $sort = NULL, $forcount = false)
    {
        $this->db->select($select_items);
        $this->db->from('courses');
        $this->db->join('entities', 'entities.guid = courses.guid');
        if ($where) {
            $this->db->where($where);
        }
        if ($orderby) {
            $this->db->orderby($orderby, $sort);
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