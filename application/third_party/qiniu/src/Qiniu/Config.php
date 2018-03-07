<?php
namespace Qiniu;

final class Config
{
    const SDK_VER = '7.0.7';

    const BLOCK_SIZE = 4194304; //4*1024*1024 分块上传块大小，该参数为接口规格，不能修改

    const IO_HOST  = 'http://iovip.qbox.me';            // 七牛源站Host
    const RS_HOST  = 'http://rs.qbox.me';               // 文件元信息管理操作Host
    const RSF_HOST = 'http://rsf.qbox.me';              // 列举操作Host
    const API_HOST = 'http://api.qiniu.com';            // 数据处理操作Host

    private $upHost;                                    // 上传Host
    private $upHostBackup;                              // 上传备用Host

    public function __construct(Zone $z = null)         // 构造函数，默认为zone0
    {
        if ($z === null) {
            $z = Zone::zone0();
        }
        $this->upHost = $z->upHost;
        $this->upHostBackup = $z->upHostBackup;
    }

    public function getUpHost()
    {
        return $this->upHost;
    }

    public function getUpHostBackup()
    {
        return $this->upHostBackup;
    }
}
