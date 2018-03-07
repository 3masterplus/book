<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/core/Api_Controller.php';
class Weixin extends Api_Controller
{
    const MP_WX_APPID  = 'wx45e3d24d9d47c52d';
    const MP_WX_SECRET = '2e65defe43982180362afd8430c38f2a';

    function __construct()
    {
        // Construct our parent class
        parent::__construct();
    }

    /**
     * 获取微信公众账号access_token
     * http://mp.weixin.qq.com/wiki/15/54ce45d8d30b6bf6758f68d2e95bc627.html
     * @return array
     */
    function token_get($isEcho = true, $force_update = false)
    {
        $t            = time();
        $new_generate = true;
        $wx_data      = array();
        $url          = 'https://api.weixin.qq.com/cgi-bin/token';
        $where        = array(
            'name' => 'mp_wx_access_token'
        );
        if (!$force_update) {
            $access_token_data = $this->my_lib->get_records('token_cache', '*', $where, 1, 0, 'time_created', 'desc');
            if ($access_token_data) {
                $time_expire  = $access_token_data[0]['time_expire'];
                $access_token = $access_token_data[0]['value'];
                if ($t < $time_expire) {
                    $new_generate = false;
                    $wx_data = array(
                        'access_token' => $access_token,
                        'expires_in' => $time_expire - $t,
                    );
                    $res = json_encode($wx_data);
                }
            }
        }
        //需要新生成access_token
        if ($new_generate || $force_update) {
            $data = array(
                'grant_type' => 'client_credential',
                'appid'      => self::MP_WX_APPID,
                'secret'     => self::MP_WX_SECRET,
            );
            $res = tran_curl_data($url, $data, 'GET');
            $wx_data = @json_decode($res, true);
            if (isset($wx_data['access_token'])  && isset($wx_data['expires_in']) ){
                $insert_data = array(
                    'name'         => 'mp_wx_access_token',
                    'value'        => $wx_data['access_token'],
                    'time_created' => $t,
                    'time_expire'  => $wx_data['expires_in'] + $t,
                );
                $this->my_lib->create_a_record('token_cache', $insert_data);
            }
        }
        if ($isEcho){
            header('Content-Type: application/json');
            exit($res);
        }else {
            return $wx_data;
        }
    }

    /**
     * 通过access_token获取js_ticket数据
     * http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html#.E9.99.84.E5.BD.951-JS-SDK.E4.BD.BF.E7.94.A8.E6.9D.83.E9.99.90.E7.AD.BE.E5.90.8D.E7.AE.97.E6.B3.95
     * @return json
     */
    function ticket_get()
    {
        header('Content-Type: application/json');
        $t            = time();
        $new_generate = true;
        $wx_data      = array();
        $where = array(
            'name' => 'mp_wx_js_ticket',
        );
        $wx_js_data = $this->my_lib->get_records('token_cache', '*', $where, 1, 0, 'time_created', 'desc');
        if ($wx_js_data) {
            $mp_wx_js_ticket = $wx_js_data[0]['value'];
            $time_expire = $wx_js_data[0]['time_expire'];
            if ($t < $time_expire) {
                $new_generate = false;
                $wx_data = array(
                    'errcode' => 0,
                    'errmsg' => "ok",
                    'ticket' => $mp_wx_js_ticket,
                    'expires_in' => $time_expire - $t,
                );
                $res = json_encode($wx_data);
            }
        }
        //重新生成新的ticket
        if ($new_generate) {
            try {
                $ac  = $this->token_get(false);
                $res = $this->update_wx_js_ticket($ac, 0);
            } catch (Exception $e) {
                exit($e->getMessage());
            }
        }
        exit($res);
    }

    function update_wx_js_ticket($access_token_data, $recursive_time = 0)
    {
        $t   = time();
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
        if (!isset($access_token_data['access_token'])){
            throw new Exception('{"errcode":10000,"errmsg":"unknow error, please try again"}');
        }
        $data = array(
            'access_token' => $access_token_data['access_token'],
            'type' => 'jsapi'
        );
        $res = tran_curl_data($url, $data, 'GET');
        $wx_data = @json_decode($res, true);
        if (isset($wx_data['ticket']) && isset($wx_data['expires_in']) ){
            $insert_data = array(
                'name'         => 'mp_wx_js_ticket',
                'value'        => $wx_data['ticket'],
                'time_created' => $t,
                'time_expire'  => $wx_data['expires_in'] + $t,
            );
            $this->my_lib->create_a_record('token_cache', $insert_data);
        }else if (isset($wx_data['errcode']) && $wx_data['errcode'] == 40001 ) {
            if ($recursive_time < 3) {
                $re_wx_data = $this->token_get(false, true);
                return $this->update_wx_js_ticket($re_wx_data);
                $recursive_time++;
            }
        }
        return $res;
    }
}