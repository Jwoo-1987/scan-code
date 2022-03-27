<?php

namespace App\Common\File;

use App\Common\ApiControl;
use \Exception;
use App\Common\File\Storage\StorageInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * 应用文件资源管理控制器
 * Class Filesystem
 * @package App\Common\File
 * @author Dyso Deng <dysodengs@gmail.com>
 * @date 2016-01-15
 */
class Filesystem
{
    /**
     * 配置项
     * @var array
     */
    protected $config = [
        'rootPath'      =>  '', //保存根路径
        'storage'       =>  '', // 文件存储驱动 File-本地存储 AliOSS-阿里云存储
        'storageConfig' =>  array(), // 上传驱动配置
        'storage_type'  =>  'panel', // 存储类型 panel-平台系统存储
    ];

    /**
     * 错误信息
     * @var string
     */
    protected $error = '';

    /**
     * 文件存储器
     * @var null
     */
    protected $storage = null;

    /**
     * 构造函数 初始化文件系统控制器
     * @param array $config
     * @throws Exception
     */
    public function __construct($config = [])
    {
        $file_storage = Config::get('filesystems.default_storage');
        $this->config = array_merge($this->config, $config);

        if(!isset($this->config['storage']) || $this->config['storage'] == ''){
            $this->config['storage'] = Config::get('filesystems.file_storage.'.$file_storage.'.storage');
        }
        if(!isset($this->config['storageConfig']) || empty($this->config['storageConfig'])){
            $this->config['storageConfig'] = Config::get('filesystems.file_storage.'.$file_storage.'.storageConfig');
        }
        if(!isset($this->config['callback'])){
            $this->config['callback'] = array($this, 'fileExists');
        }
        if(!isset($this->config['removeTrash'])){
            $this->config['removeTrash'] = array($this, 'removeTrash');
        }

        if(!isset($this->config['rootPath']) || $this->config['rootPath'] == ''){
            switch($this->config['storage_type']){
                case 'panel':
                    $rootPath = $this->config['storage_type'];
                    break;
                default:
                    $rootPath = 'panel';

            }
            $this->config['rootPath'] = $rootPath;
        }
        $this->config['rootPath'] = '/'.ltrim(ltrim(rtrim($this->config['rootPath'], '/').'/', './'), '/');

        //初始化文件存储器
        $storageClass = "\\App\\Common\\File\\Storage\\".ucfirst(strtolower($this->config['storage'])).'Storage';
        $this->storage = new $storageClass($this->config['storageConfig']);

        if(!$this->storage || !$this->storage instanceof StorageInterface){
            Log::info('文件存储器不存在');
            throw new \Exception('文件存储器不存在', 1);
        }
    }

    /**
     * 文件上传 (可上传多文件)
     * @param string $files     上传文件信息
     * @return array|bool       文件信息
     */
    public function upload($files = '')
    {
        if($files == ''){
            $files = $_FILES;
        }

        $file = new Uploader($this->storage, $this->config);
        $fileInfo = $file->upload($files);
        if($fileInfo === false){
            $this->error = $file->getError();
            return false;
        }

        foreach($fileInfo as $key=>&$value){
            /* 已经存在文件记录 */
            if(isset($value['id']) && is_numeric($value['id'])){
                continue;
            }

            $value['fullpath'] = ltrim($this->config['rootPath'], '/').$value['savepath'].$value['savename'];
            $value['mime'] = $value['type'];
            unset($value['key']);
            unset($value['type']);
            //保存至数据库
            switch($this->config['storage_type']){
                case 'panel':
                    $value['storage_type'] = $this->config['storage_type'];
                    $result = ApiControl::getApiUrl('file/create', $value, 'post', 'panel');
                    if($result['status'] == 1){
                        $value['id'] = $result['data'];
                    }else{
                        //记录数据库失败
                        unset($fileInfo[$key]);
                        Log::error('file_save_error:'.$result['errMsg'].' line '.__LINE__);
                    }
                    break;
            }
        }

        return $fileInfo;
    }

    /**
     * 删除文件
     */
    public function deleteFile(){}

    /**
     * 下载文件
     */
    public function download(){}

    /**
     * 文件列表
     */
    public function filelist(){}

    /**
     * 检测文件是否存在，如存在则返回文件信息
     * @param array $fileData           文件资源信息
     * @return bool|mixed
     */
    public function fileExists(array $fileData)
    {
        switch($this->config['storage_type']){
            case 'panel':
                $data = [
                    'sha1'=>$fileData['sha1'],
                    'md5'=>$fileData['md5'],
                    'storage_type'=>$this->config['storage_type']
                ];
                if($this->config['storage_type'] == 'user'){
                    $data['user_id'] = $this->config['user_id'];
                }
                if($this->config['storage_type'] == 'shop'){
                    $data['shop_id'] = $this->config['shop_id'];
                }
                if($this->config['storage_type'] == 'app'){
                    $data['app_name'] = $this->config['app_name'];
                }
                $result = ApiControl::getApiUrl('file/info/by_hash', $data, 'get', 'panel');
                if($result['status'] == 1){
                    $result = $result['data'];
                }else{
                    $result = false;
                }
                break;
            default:
                $result = false;
        }
        return $result;
    }

    /**
     * 删除数据库存在但存储器不存在的数据
     * @param array $fileData       文件资源信息
     */
    public function removeTrash(array $fileData)
    {
        switch($this->config['storage_type']){
            case 'panel':
                ApiControl::getApiUrl('file/delete', ['id'=>$fileData['id'], 'storage_type'=>$this->config['storage_type']], 'post');
                break;
        }
    }

    public function getError()
    {
        return $this->error;
    }

}