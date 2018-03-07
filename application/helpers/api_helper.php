<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

function api_post($str)
{
    return set_value($str) ? set_value($str) : ci_post($str);
}


function api_get($str)
{
    return set_value($str) ? set_value($str) : ci_get($str);
}


/**
 * 基本的数据输出格式
 * @param  [type] $data   [description]
 * @param  string $msg    [description]
 * @param  [type] $cursor [description]
 * @param  array  $errors [description]
 * @return [type]         [description]
 */
function basic_output_func($data = null, $msg = "", $cursor = null, $errors = array() )
{
    $data   = $data ? $data : null;
    $cursor = $cursor ? $cursor : null;
    $msg    = $msg ? $msg : "";

    switch ($errors) 
    {
        case true:
            $result = 'no_ok';
            break;
        case false:
            $result = 'ok';
            break;
    }
    $response = array(
        'result'   => $result,
        'data'     => $data,
        'cursor'   => $cursor,
        'success'  => tmt_lang($msg),
        'errors'   => $errors,
    );
    return $response;
}

/**
 * 该方法用于输出成功信息
 * @param  [type] $data   [description]
 * @param  string $msg    [description]
 * @param  [type] $cursor [description]
 * @param  array  $errors [description]
 * @return [type]         [description]
 */
function echoSucc($msg = "", $data = null, $cursor = null, $code = '200', $errors = array() )
{
    $ci  = & get_instance();
    $res = basic_output_func($data, $msg, $cursor, $errors);
    
    if (isset($ci->_output) && $ci->_output ) {
        $ci->response($res, $code);
    }else {
        return true;
    }
}


/**
 * 该方法仅供处理错误信息输出
 * @param  string $field 错误位置
 * @param  string $msg   错误描述
 * @param  string $code  错误代码
 * @return json
 */
function echoErr($field = NULL, $msg = 'unknown error', $code = '406')
{
    $ci    = & get_instance();
    $field = is_array($field) ? $field : array($field);
    $msg   = is_array($msg) ? $msg : array($msg) ;
    $field = empty($field) ? array('unknown error') : $field;
    $msg   = empty($msg) ? array('unknown error') : $msg;


    foreach ($msg as $k=>$v){
        $err[] = array(
            'field' => $field[$k],
            'message' => lang($v) ? lang($v) : $v,
        );
    }
    $response = basic_output_func(null, "", null, $err);
    if (isset($ci->_output) && $ci->_output ) {
        $ci->response($response, $code);
    }else {
        return $response;
    }
}


/**
 * 识别字符串类型的布尔结果
 * @return boolean
 */
function boolformat($str = NULL)
{
    $arr = array_merge($_GET, $_POST);
    if ( !isset($arr[$str]) ){
        return NULL;
    }
    $str = $arr[$str];
    if (strtolower($str) === 'true'){
        return true;
    }
    else if ($str === '1'){
        return true;
    }
    else if ($str === true){
        return true;
    }
    else{
        return false;
    }
}


/**
 * 获取nginx环境下的真实IP
 */
function get_real_nginx_ip()
{
    $ip_address = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : NULL;
    $ip_address = explode(',' , $ip_address);
    foreach ($ip_address as $k=>$ip){
        if (!preg_match('/^(10\.|172\.|192\.|:).*$/', $ip) && $ip ){
            return $ip;
        }
    }
    $ci=& get_instance();
    return $ci->input->ip_address();
}


/**
 * 多语言
 * @param  [type] $msg [description]
 * @return [type]      [description]
 */
function tmt_lang($msg = NULL)
{
    if (!$msg){
        return $msg;
    }
    return lang($msg) ? lang($msg) : $msg;
}


/**
 * 处理表单错误信息输出
 * @return boolean
 */
function ParamErr($config_name, $method, $httpcode = 400)
{
    $func = strtolower($method) == 'post' ? 'post' : 'get';
    $ci = & get_instance();
    $ci->config->load('form_validation', true);
    $fv_config = $ci->config->config['form_validation'];
    
    if (!isset($fv_config[$config_name])) {
        return true;
    }else {
        $validation_data = array();
        foreach ($fv_config[$config_name] as $k=>$v){
            $field = $v['field'];
            $validation_data[$field] = $ci->input->$func($field);
        }
        $ci->form_validation->set_data($validation_data);
    }
    $ck = $ci->form_validation->run($config_name);
    $ci->form_validation->clear_field_data();
    if ($ck) {
        return true;
    }else {
        
        if (FALSE === ($OBJ =& _get_validation_object()))
        {
            $err = array();
        }else{
            $err = $OBJ->error_array();
        }
        $field = $msg = array();
        if (is_array($err) && !empty($err)){
            foreach ($err as $k=>$e){
                $field[] = $k;
                $k_zh = lang($k) === false ? $k : lang($k);
                $e = str_replace($k, $k_zh, $e);
                $msg[] = $e;
            }
        }
        echoErr($field, $msg, $httpcode);
    }
}


/**
 * 随机生成字符串
 * @param  integer $len 
 * @return string
 */
function generate_token ($len = 32)
{

    // Array of potential characters, shuffled.
    $chars = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
    );

    shuffle($chars);

    $num_chars = count($chars) - 1;
    $token = '';

    // Create random token at the specified length.
    for ($i = 0; $i < $len; $i++)
    {
        $token .= $chars[mt_rand(0, $num_chars)];
    }

    return $token;
}



/**
 * 获取header中单个key值
 * @param  [type] $key [description]
 * @return [type]      [description]
 */
function getHeaderValue($key = NULL)
{
    if (!$key) return NULL;

    $key = ucfirst($key);
    $headers = NULL;
    if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();

    }else {
        $requestHeaders = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                // $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                $requestHeaders[strtolower(substr($name, 5))] = $value;
            }
        }
    }
    
    // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
    $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
    if (isset($requestHeaders[$key])) {
        $headers = trim($requestHeaders[$key]);
    }
    return $headers;
}


/**
 * 删除二维数组中指定的键值对
 * @param  array  $arr  待处理二维数组
 * @param  array  $field 待删除的二维数组中的键名
 * @return 
 */
function remove_two_dimensional_arr_field( & $arr = array(), $field = array() )
{
    if (!is_array($arr)) {
        return $arr;
    }
    if (empty($arr)) {
        return $arr;
    }
    if (!$field) {
        return $arr;
    }
    $field = is_array($field) ? $field : array($field);
    foreach ($arr as $k=>$v) {
        foreach ($field as $v2) {
            unset($arr[$k][$v2]);
        }
    }
}
