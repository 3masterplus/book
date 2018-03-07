<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

	class Sns extends Client_Controller {

    public function __construct()
    {
          parent::__construct();
        //$this->session->userdata('is_login') AND redirect();
    }

    public function session($provider = '')
    {
    	$refer = $this->session->userdata('refer');
	    if( !$refer ){
	      $refer = CI_GET('refer') ? CI_GET('refer') : '/';
	      $this->session->set_userdata('refer', $refer);
	    }

    	$redirect_url = '/user/sns/'.$provider.'?refer='.$refer;

        //如果用户已登录，执行绑定操作
        if( $this->session->userdata('user')['access_token'] ){
          $this->session->set_userdata('sns_action', 'just_bind');
        }

        $this->config->load('oauth2');
        $allowed_providers = $this->config->item('oauth2');
    
        if ( ! $provider OR ! isset($allowed_providers[$provider]))
        {
            $this->session->set_flashdata('info', '暂不支持'.$provider.'方式登录.');
            //var_dump('111');exit;
            redirect();
            return;
        }
        $this->load->library('oauth2');
        $provider = $this->oauth2->provider($provider, $allowed_providers[$provider]);
        $args = $this->input->get();
        if ($args AND !isset($args['code']))
        {
            $this->session->set_flashdata('info', '授权失败了,可能由于应用设置问题或者用户拒绝授权.<br />具体原因:<br />'.json_encode($args));
           //var_dump('222');exit;
            redirect();
            return;
        }
        $code = $this->input->get('code', TRUE);
        if ( ! $code)
        {
            $provider->authorize();
            //var_dump('333');exit;
            return;
        }
        else
        {
            try
            {
                $token = $provider->access($code);
                $sns_user = $provider->get_user_info($token);
                if (is_array($sns_user))
                {
                    $this->session->set_flashdata('info', '登录成功');
                    $this->session->set_userdata('user', $sns_user);
                    $this->session->set_userdata('is_login', TRUE);
                }
                else
                {
                    $this->session->set_flashdata('info', '获取用户信息失败');
                }
            }
            catch (OAuth2_Exception $e)
            {
                $this->session->set_flashdata('info', '操作失败<pre>'.$e.'</pre>');
            }
        }
        redirect($redirect_url);
    }
}