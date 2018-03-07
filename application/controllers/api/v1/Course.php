<?php defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/core/Api_Controller.php';

class Course extends Api_Controller
{
    /**
     * 默认分页数
     * @var integer
     */
    private $limit = 10;

    /**
     * 默认偏移量
     * @var integer
     */
    private $offset = 0;

    /**
     *  构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('course_lib');
        $this->load->library('api/api_course_lib');
    }


    /**
     * 获取移动端首页列表
     * @return json
     */
    public function home_get()
    {
        ParamErr('api/course/list_get', 'get', 500);
        $limit  = api_get('limit') ? api_get('limit') : $this->limit;
        $offset = api_get('offset') ? api_get('offset') : $this->offset;

        $select_items  = 'courses.unique_key as course_unique_key,courses.title as course_title, courses.main as course_summary, courses.publish_option, courses.is_course_free, courses.fee_policy, courses.by_course_fee, courses.access';
        $select_items .= ',courses.guid';

        $where = array(
            'courses.status' => 'PUBLISHED',
        );
        $courses = $this->my_lib->get_records('courses', $select_items, $where, $limit, $offset, NULL, NULL);
        $counts  = $this->my_lib->get_records('courses', 'courses.guid', $where, NULL, 0, NULL, NULL, true);
        $courses = $this->api_course_lib->get_course_more_info($courses);
        $cursor  = array(
            'total'  => $counts,
            'limit'  => $limit,
            'offset' => $offset,
        );
        echoSucc('', $courses, $cursor);
    }


    /**
     * 获取我的课程列表【等待pc端...】
     * @return [type] [description]
     */
    public function my_course_list_get()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();

        ParamErr('api/course/my_list_get', 'get', 500);
        $limit  = api_get('limit') ? api_get('limit') : $this->limit;
        $offset = api_get('offset') ? api_get('offset') : $this->offset;
        $status = api_get('status') ? api_get('status') : NULL;

        $select_items  = 'courses.unique_key as course_unique_key,courses.title as course_title, courses.main as course_summary, courses.publish_option, courses.is_course_free, courses.fee_policy, courses.by_course_fee, courses.access';
        $select_items .= ',courses.guid';

        $where = array(
            // 'courses.status' => 'PUBLISHED',
            'user_course_relations.user_guid' => $this->_guid,
        );
        $courses = $this->api_course_lib->get_my_courses($select_items, $where, $limit, $offset, NULL, NULL);
        $counts  = $this->api_course_lib->get_my_courses('courses.guid', $where, NULL, 0, NULL, NULL, true);
        $courses = $this->api_course_lib->get_course_more_info($courses);
        $cursor  = array(
            'total'  => $counts,
            'limit'  => $limit,
            'offset' => $offset,
        );
        echoSucc('', $courses, $cursor);
    }


    /**
     * 获取当前用户未学完的课程
     * @return json
     */
    public function uncompleted_courses_get()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        $courses = $this->course_lib->get_user_uncompleted_courses($this->_guid);
        remove_two_dimensional_arr_field($courses, 'course_guid' );
        echoSucc('', $courses);   
    }

    /**
     * 获取当前用户已经学完的课程
     * @return json
     */
    public function completed_courses_get()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        $courses = $this->course_lib->get_completed_course($this->_guid);
        remove_two_dimensional_arr_field($courses, 'course_guid' );
        echoSucc('', $courses);
    }


    /**
     * 获取一个课程基本信息
     * @param  string $course_unique_key 课程唯一识别码
     * @return json
     */
    public function course_get($course_unique_key = null)
    {
        //判断当前用户是否是该课程的作者
        // $attr = $this-> check_course_attr();

        if (!$course_unique_key) {
            echoErr();
        }
        //获取课程的基本信息
        $course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key);

        //只允许获取公开的课程
        if (!$course || $course['status'] != 'PUBLISHED') {
            echoErr('course no found', 'couse no found');
        }
        $course_guid = $course['guid'];

        //获取课程老师的GUID
        $owner_guid = $this->my_lib->get_a_value('entities', 'owner_guid', array('unique_key' => $course_unique_key));

        //获取这位老师的信息
        $lecturer = $this->my_lib->get_a_subtype_row('users', (int) $owner_guid);
        
        //获取老师头像
        $avatar_relative = $this->my_lib->get_user_avatar($lecturer['unique_key']);
        $lecturer_avatar = $avatar_relative ? $avatar_relative : NULL;
        
        $lecturer_arr[] = array(
            'lecturer_username' => $lecturer['username'],
            'lecturer_bio' => $lecturer['bio'],
            'lecturer_signature' => $lecturer['signature'],
            'lecturer_avatar' => $lecturer_avatar,
        );

        //获取节点数组
        $syllabus = $this->course_lib->get_syllabus_menu_for_course_home($course_guid);
        
        //计算出整个课程的节点数
        // $course_count = $this->multi_array_count($syllabus);

        //获取该用户对一个用户的完成度
        $percentage_of_course_completion = $this->course_lib->get_percentage_of_course_completion($this->_guid, $course_guid);

        //获取课程的收费情况
        $course_data = array(
            'course_guid'       => $course_guid,
            'course_unique_key' => $course_unique_key,
            'publish_option'    => $course['publish_option'],
            'is_course_free'    => $course['is_course_free'],
            'fee_policy'        => $course['fee_policy'],
            'by_course_fee'     => $course['by_course_fee'],
            'access'            => $course['access']
        );

        $syllabus = $this->course_lib->get_syllabus_menu_for_course_home($course_guid);
        $syllabus = $this->course_lib->check_if_node_learned($syllabus, $this->_guid);

        try {
            $accessibility = $this->course_lib->check_accessibility($course_data, $this->_guid, $syllabus);
            if (!$accessibility) {
                throw new Exception("课程收费规则暂未确定，请稍后再来查看");
            }
        } catch (Exception $e) {
            $exception_err_msg = $e->getMessage();
            echoErr($exception_err_msg, $exception_err_msg);
        }
        $data = array(
            'course_title'                    => $course['title'],
            'course_summary'                  => $course['main'],
            'course_description'              => str_replace(chr(10) . chr(13), '</p><p>', $course['description']),
            'course_audience'                 => $course['audience'],
            'course_objectives'               => $course['objectives'],
            'course_publish_option'           => $course['publish_option'],
            'course_is_course_free'           => $course['is_course_free'],
            'course_by_course_fee'            => $course['by_course_fee'],
            'course_number_of_participants'   => 100,
            'course_number_of_sections'       => $this->course_lib->get_course_number_of_sections($course_guid),
            'course_number_of_nodes'          => $this->course_lib->get_course_number_of_nodes($course_guid),
            'course_number_of_questions'      => $this->course_lib->get_course_number_of_questions($course_guid),
            'lecturer'                        => $lecturer_arr,
            'percentage_of_course_completion' => $percentage_of_course_completion,
            'accessibility'                   => $accessibility,
        );
        echoSucc('', $data);
    }

    /**
     * 获取一个课程的章节列表
     * @param  string $course_unique_key 课程唯一识别码
     * @return json
     */
    public function sections_list_get($course_unique_key = NULL)
    {
        if (!$course_unique_key) {
            echoErr();
        }
        //获取课程的基本信息
        $course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key);

        //只允许获取公开的课程
        if (!$course || $course['status'] != 'PUBLISHED') {
            echoErr('course no found', 'couse no found');
        }

        //初始化输入参数
        $offset = api_get('offset');
        $limit  = api_get('limit');

        $course_guid = $course['guid'];

        $course_sections = $this->course_lib->get_sections($course_guid);

        //补充客户端额外需要的字段数据
        $course_sections = $this->api_course_lib->get_more_section_info($course_sections);

        remove_two_dimensional_arr_field($course_sections, 'guid' );
        $section_count = count($course_sections);
        $cursor = array(
            'total'  => $section_count,
            'limit'  => 0,
            'offset' => 0,
        );
        echoSucc('', $course_sections, $cursor);
    }


    /**
     * 获取一个章节下面的节点列表
     * @param  string $section_unique_key 章节唯一识别码
     * @return json
     */
    public function nodes_list_get($section_unique_key = NULL)
    {
        if (!$section_unique_key) {
            echoErr();
        }
        //获取课程章节的基本信息
        $section = $this->my_lib->get_a_subtype_row('sections', $section_unique_key);

        //检测章节是否存在
        if (!$section) {
            echoErr('section no found', 'section no found');
        }

        $course_guid  = $section['course_guid'];
        $section_guid = $section['guid'];

        //获取课程的基本信息
        $course = $this->my_lib->get_a_subtype_row('courses', (int)$course_guid);
        
        //只允许获取公开的课程
        if (!$course || $course['status'] != 'PUBLISHED') {
            echoErr('course no found', 'couse no found');
        }

        // $nodes  = $this->course_lib->get_syllabus_menu_for_course_info($section_guid);
        $nodes  = $this->course_lib->get_syllabus_menu_for_course_home($section_guid);

        //获取节点额外的信息
        $nodes  = $this->api_course_lib->get_more_node_info($nodes);

        $counts = count($nodes);

        $cursor = array(
            'total'  => $counts,
            'limit'  => 0,
            'offset' => 0,
        );
        echoSucc('', $nodes, $cursor);
    }


    //计算一个多维数组中的elements
    private function multi_array_count($array, $count = 0)
    {           
        foreach($array AS $row)
        {
            $count++;   
            if(count($row['child']) > 0)
            {
                $count = $this->multi_array_count($row['child'], $count);
            }
        }
        return $count;
    }


    /**
     * 获取节点详情内容
     */
    public function node_detail_get($node_unique_key = NULL)
    {
        if (!$node_unique_key) {
            echoErr();
        }
        //获取课程章节的基本信息
        $node_data = $this->my_lib->get_a_subtype_row('nodes', $node_unique_key);

        //检测章节是否存在
        if (!$node_data) {
            echoErr('node no found', 'node no found');
        }

        $course_guid = $node_data['course_guid'];
        $section_guid = $node_data['section_guid'];
        $node_guid = $node_data['guid'];
        $father_guid = $node_data['father_guid'];

        $course_data = $this->my_lib->get_a_subtype_row('courses', (int)$course_guid );
        $section_data = $this->my_lib->get_a_subtype_row('sections', (int)$section_guid );
        $father_data = $this->my_lib->get_a_subtype_row('nodes', (int)$father_guid );

        $output = array(
            'node_unique_key'        => $node_unique_key,
            'section_unique_key'     => $section_data['unique_key'],
            'course_unique_key'      => $course_data['unique_key'],
            'father_node_unique_key' => $father_data ? $father_data['unique_key'] : NULL,
            'title'                  => $node_data['title'],
            'main'                   => $node_data['main'],
        );
        echoSucc('', $output);
    }


    /**
     * 获取一个节点下的所有问题列表
     * @param  string $node_unique_key 节点unique_key
     * @return json
     */
    public function quizzes_list_get($node_unique_key = NULL)
    {
        $this->load->library('quiz_lib');

        if (!$node_unique_key) {
            echoErr();
        }
        //获取课程章节的基本信息
        $node_data = $this->my_lib->get_a_subtype_row('nodes', $node_unique_key);

        //检测章节是否存在
        if (!$node_data) {
            echoErr('node no found', 'node no found');
        }

        $node_guid = $node_data['guid'];

        //获取“node”下的测试题
        $quizzes = $this->quiz_lib->get_quizzes($node_guid);

        remove_two_dimensional_arr_field($quizzes, 'guid');

        echoSucc('', $quizzes);
    }


    /************************************************
                  一些POST操作
    *************************************************/

    /**
     * 加入一门课程学习
     * @return json
     */
    function join_course_post($course_unique_key = NULL)
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();

        if (!$course_unique_key) {
            echoErr('course_unique_key error', 'course_unique_key error');
        }
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        // 获取课程的相关信息
        $course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, array('guid', 'is_course_free'));
        if (!$course) {
            echoErr('course no found', '课程不存在');
        }
        $is_course_free = $course['is_course_free'];
        $course_guid = $course['guid'];
        // 如果用户已经加入了这个课程，告知用户，他已经加入了该课程；
        if($this->course_lib->if_a_course_joined($this->_guid, $course_guid)) {
            echoErr('have joined this course', '您已加入该课程，不能反复加入');
        }
        // 如果课程不免费，则需要用户支付该课程费用
        // if(!$is_course_free) {
        //     //判断用户是否已经支付了该课程
        // }else {
        // }
        $this->course_lib->join_a_course($this->_guid, $course_guid);
        echoSucc();
    }

    /**
     * 退出一门课程学习
     * @return json
     */
    function join_course_delete($course_unique_key = NULL)
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();

        if (!$course_unique_key) {
            echoErr('course_unique_key error', 'course_unique_key error');
        }
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();
        // 获取课程的相关信息
        $course = $this->my_lib->get_a_subtype_row('courses', $course_unique_key, array('guid', 'is_course_free'));
        if (!$course) {
            echoErr('course no found', '课程不存在');
        }
        $is_course_free = $course['is_course_free'];
        $course_guid = $course['guid'];
        // 如果用户已经加入了这个课程，告知用户，他已经加入了该课程；
        if(!$this->course_lib->if_a_course_joined($this->_guid, $course_guid)) {
            echoErr('have not joined this course', '您尚未加入该课程');
        }
        // 如果课程不免费，则需要用户支付该课程费用
        // if(!$is_course_free) {
        //     //判断用户是否已经支付了该课程
        // }else {
        // }
        $this->course_lib->quit_a_course($this->_guid, $course_guid);
        echoSucc();
    }


    /**
     * 标记node已学完
     * @return json
     */
    function learn_node_post()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();

        $user_guid       = $this->_guid;
        $node_unique_key = api_post('node_unique_key');
        $node_guid       = $this->my_lib->get_guid_by_unique_key($node_unique_key);
        if (!$node_guid) {
            echoErr('nodes no found', 'nodes no found');
        }
        $node_data    = $this->my_lib->get_records('nodes', 'course_guid,section_guid', array('guid' => $node_guid), 1, 0 );
        if (!$node_data) {
            echoErr('nodes no found', 'nodes no found');
        }
        $course_guid  = $node_data[0]['course_guid'];
        $section_guid = $node_data[0]['section_guid'];
        $this->course_lib->learn_a_node($course_guid, $section_guid, $node_guid, $user_guid);
        echoSucc();
    }

    /**
     * 标记node未学完
     * @return json
     */
    function unlearn_node_delete()
    {
        //need to check user access token
        $this->api_base_lib->checkAccesstoken();

        $user_guid       = $this->_guid;
        $node_unique_key = api_post('node_unique_key');
        $node_guid       = $this->my_lib->get_guid_by_unique_key($node_unique_key);
        if (!$node_guid) {
            echoErr('nodes no found', 'nodes no found');
        }
        $node_data    = $this->my_lib->get_records('nodes', 'course_guid,section_guid', array('guid' => $node_guid), 1, 0 );
        if (!$node_data) {
            echoErr('nodes no found', 'nodes no found');
        }
        $course_guid  = $node_data[0]['course_guid'];
        $section_guid = $node_data[0]['section_guid'];
        $this->course_lib->unlearn_a_node($user_guid, $node_guid);
        echoSucc();
    }



    /**
     * 发布的课程列表
     * @return json
     */
    public function pub_courses_get($user_unique_key)
    {
        $_GET['user_unique_key'] = $user_unique_key;

        ParamErr('api/course/pub_courses_get', 'get', 500);
        $limit  = api_get('limit') ? api_get('limit') : 10;
        $offset = api_get('offset') ? api_get('offset') : 0;

        $uid = $this->my_lib->get_a_value('users', 'guid', array('unique_key' => $user_unique_key));
        if (!$uid){
            echoErr('user no found', 'user no found');
        }
        $select_items  = 'courses.unique_key as course_unique_key,courses.title as course_title, courses.main as course_summary, courses.publish_option, courses.is_course_free, courses.fee_policy, courses.by_course_fee, courses.access';
        $select_items .= ',courses.guid';
        $where = array(
            'courses.status' => 'PUBLISHED',
            'entities.owner_guid' => $uid,
        );
        $courses = $this->api_course_lib->get_courses_with_entities($select_items, $where, $limit, $offset, NULL, NULL, false);
        $courses = $this->api_course_lib->get_course_more_info($courses);
        echoSucc('', $courses);
    }
}
