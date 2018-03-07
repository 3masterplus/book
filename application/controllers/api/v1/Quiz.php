
<?php defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . '/core/Api_Controller.php';

class Quiz extends Api_Controller
{
    /**
     *
     *  构造函数
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('quiz_lib');
    }

    
    //获取一个用户某次填空题的答案 ? 这个time_created 客户端怎么可能知道？
    public function answers_gap_get()
    {
        ParamErr('api/quiz/answers_gap_get', 'get', 500);

        $time_created        = api_get('time_created');
        $question_unique_key = api_get('question_unique_key');
        $question_guid       = $this->my_lib->get_guid_by_unique_key($question_unique_key);
        $condition           = array('time_created' => $time_created, 'user_guid' => $this->_guid, 'question_guid' => $question_guid);
        $fields              = array('gap_key', 'answer', 'result');
        $result              = $this->my_lib->get_records('user_question_relations_gaps', $fields, $condition);
        echoSucc('', $result);
    }

    /**
     * 获取一个填空题的详细信息
     * @return 
     */
    public function gap_get()
    {
        ParamErr('api/quiz/gap_get', 'get', 500);
        $question_unique_key = api_get('question_unique_key');
        $result = $this->quiz_lib->get_a_question_with_gaps($question_unique_key);
        echoSucc('', $result);
    }

    /**
     * 获取一个选择题的详细信息
     * @return json
     */
    public function multiple_options_get()
    {
        ParamErr('api/quiz/multiple_options_get', 'get', 500);
        $question_unique_key = api_get('question_unique_key');
        $result = $this->quiz_lib->get_a_question_with_multiple_options($question_unique_key);
        $data['options'] = $result;
        echoSucc('', $data);
    }

}