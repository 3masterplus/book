<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

use \Curl\Curl;

class Api_image_lib extends my_lib
{
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    /**
     * 保存远程图片到本地
     * @param  string  $remote_imgurl 远程图片绝对地址
     * @param  string  $path          本地需要保存的相对地址
     * @param  boolean $is_to_db      是否需要将图片保存到数据库中
     * @return boolean                [description]
     */
    public function save_img_to_local($remote_imgurl = null, $suffix_path = '')
    {
        $imgdata = array();

        $curl = new Curl();
        $curl->get($remote_imgurl);

        if ($curl->error) {
            return false;
            // echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage;
        } else {
            $imgdata = $curl->response;
        }

        $imgheader = $curl->responseHeaders;
        $mime = $imgheader['Content-Type'];

        if (!in_array($mime, array('image/jpeg', 'image/png', 'image/gif'))) {
            return false;
        }
        #初始化本地存储参数
        $t = time();
        $ext = str_ireplace('image/', '.', $mime);
        $suffix_path = preg_replace('/\/+$/', '', $suffix_path);
        $suffix_path = preg_replace('/^\/+/', '', $suffix_path);
        $full_path = IMGPATH . $suffix_path . '/';
        $filename = md5($remote_imgurl) . '_' . $t . $ext;
        $full_name = $full_path . $filename;
        //创建保存地址
        if (!is_dir($full_path)) {
            if (!@mkdir($full_path, 0755, true)) {
                echoErr('permission deny', "{$full_path} not writable");
            }
        }
        //保存图片到本地
        $fh = @fopen($full_name, 'a');
        if (!$fh) {
            throw new Exception("{$full_name} can not created, please check the permission");
        }
        fwrite($fh, $imgdata);
        fclose($fh);
        $res = array(
            'full_name' => $full_name,
            'full_path' => $full_path,
            'relative_name' => $suffix_path . '/' . $filename,
            'relative_path' => $suffix_path . '/',
            'filename' => $filename,
            'ext' => $ext,
            'mime' => $mime,
        );
        return $res;
    }
}
