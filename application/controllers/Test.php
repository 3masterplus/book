<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

use Pheanstalk\Pheanstalk;

class Test extends Client_Controller{
    
    function __construct()
    {
        parent::__construct();
        $this->load->library('notify_lib');
    }

    function notify_set($receiver)
    {
        $this->load->library('notify_lib');
        $this->notify_lib->set(
            '0',
            $receiver,
            'notice',
            'new_course_pub',
            ['course_guid' => '555']
        );
        echo 'ok';
    }
}
