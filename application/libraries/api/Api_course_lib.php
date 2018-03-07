<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Api_course_lib extends my_lib
{
    public function __construct()
    {
        parent::__construct();
        $this->ci = & get_instance();
        $this->ci->load->model('api/api_course_model');
    }


    public function get_course_more_info($courses)
    {
        if (!$courses) {
            return $courses;
        }
        foreach ($courses as $k=>$v) {
            $course_guid                                    = $v['guid'];
            $courses[$k]['course_number_of_sections']       = $this->ci->course_lib->get_course_number_of_sections($course_guid);
            $courses[$k]['course_number_of_nodes']          = $this->ci->course_lib->get_course_number_of_nodes($course_guid);
            $courses[$k]['course_number_of_questions']      = $this->ci->course_lib->get_course_number_of_questions($course_guid);
            $courses[$k]['percentage_of_course_completion'] = $this->ci->course_lib->get_percentage_of_course_completion($this->_guid, $course_guid);

            //获取课程的老师信息
            $owner_guid = $this->ci->my_lib->get_a_value('entities', 'owner_guid', array('guid' => $course_guid));
            $lecturer = $this->ci->my_lib->get_a_subtype_row('users', (int) $owner_guid);
            //获取老师头像
            $avatar_relative = $this->ci->my_lib->get_user_avatar($lecturer['unique_key']);
            $lecturer_avatar = $avatar_relative ? $avatar_relative : NULL;
            $lecturer_arr[] = array(
                'lecturer_username' => $lecturer['username'],
                'lecturer_bio' => $lecturer['bio'],
                'lecturer_signature' => $lecturer['signature'],
                'lecturer_avatar' => $lecturer_avatar,
            );
            $courses[$k]['lecturer'] = $lecturer_arr;

            //获取课程的收费情况
            $course_data = array(
                'course_guid'       => $course_guid,
                'course_unique_key' => $v['course_unique_key'],
                'publish_option'    => $v['publish_option'],
                'is_course_free'    => $v['is_course_free'],
                'fee_policy'        => $v['fee_policy'],
                'by_course_fee'     => $v['by_course_fee'],
                'access'            => $v['access']
            );
            $syllabus = $this->ci->course_lib->get_syllabus_menu_for_course_home($course_guid);
            $syllabus = $this->ci->course_lib->check_if_node_learned($syllabus, $this->_guid);
            $accessibility = $this->ci->course_lib->check_accessibility($course_data, $this->_guid, $syllabus);
            $courses[$k]['accessibility'] = $accessibility;
            unset($courses[$k]['guid'],$courses[$k]['is_course_free'],$courses[$k]['fee_policy'],$courses[$k]['by_course_fee'],$courses[$k]['access']);
        }
        return $courses;
    }


    /**
     * 获取sections额外的信息
     * @param  array $sections
     * @return 
     */
    public function get_more_section_info($sections)
    {
        if (!$sections) {
            return $sections;
        }

        foreach ($sections as $k => $v) {
            $guid       = isset($v['guid']) ? $v['guid'] : NULL;
            $unique_key = isset($v['unique_key']) ? $v['unique_key'] : NULL;
            //guid不存在时，手动获取guid
            if (!$guid && $unique_key) {
                $guid = $this->ci->my_lib->get_a_value('entities', 'guid',  array('unique_key' => $unique_key) );
            }
            //当前用户是否支付了该章节
            $sections[$k]['if_a_section_paid'] = $this->ci->course_lib->if_a_section_paid($this->ci->_guid, $guid);
            //当前用户在该章节的学习进度
            $sections[$k]['percentage_of_section_completion'] = $this->ci->course_lib->get_percentage_of_section_completion($this->ci->_guid, $guid);
            //获取当前章节下的节点数
            $sections[$k]['section_number_of_nodes'] = $this->ci->course_lib->get_section_number_of_nodes($guid);
        }
        return $sections;
    }

    /**
     * 获取node的额外信息
     * @param  array $nodes
     * @return array
     */
    public function get_more_node_info($nodes)
    {
        if (!$nodes) {
            return $nodes;
        }
        foreach ($nodes as $k => $v) {
            $nodes[$k] = $this->recur_get_node_info($v);
        }
        return $nodes;
    }

    /**
     * 以递归的方式获取node更多的信息
     * @param  array $node 
     * @return array
     */
    public function recur_get_node_info($node)
    {
        $guid       = isset($node['guid']) ? $node['guid'] : NULL;
        $unique_key = isset($node['unique_key']) ? $node['unique_key'] : NULL;
        //guid不存在时，手动获取guid
        if (!$guid && $unique_key) {
            $guid = $this->ci->my_lib->get_a_value('entities', 'guid',  array('unique_key' => $unique_key) );
        }

        $node['if_a_node_learned'] = $this->ci->course_lib->if_a_node_learned($this->ci->_guid, $guid);
        // $node['father_unique_key'] = $this->ci->my_lib->get_a_value('entities', 'unique_key', array('guid' => $node['father_guid']) );


        //开始递归
        if (isset($node['child']) && $node['child'] ) {

            $child = $node['child'];

            foreach ($child as $k=>$v) {
                $node['child'][$k] = $this->recur_get_node_info($v);
            }
        }
        return $node;
    }

    /**
     * 获取我的课程列表
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
        $courses = $this->ci->api_course_model->get_my_courses($select_items, $where, $limit, $offset, $orderby, $sort, $forcount);

        return $courses;
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
        return $this->ci->api_course_model->get_courses_with_entities($select_items, $where, $limit, $offset, $orderby, $sort, $forcount);
    }

}