<?php

namespace App\Common\File\Storage;

use \Exception;
use App\Common\Support\AliOSS\ALIOSS;

/**
 * 阿里云存储器
 * Class AliOSS
 * @package App\Common\File\Storage
 * @author Dyso Deng <dysodengs@gmail.com>
 * @date 2016-01-15
 */
class AliossStorage implements StorageInterface
{
    use StorageTrait;

    protected $storage = null;
    protected $bucket_name = '';
    protected $options = [];
    protected $rootPath = '';
    protected $error = '';

    /**
     * 构架函数，用于设置存储配置
     * @param array $config
     * @throws Exception
     */
    public function __construct($config = [])
    {
        if(!isset($config['options'])){
            throw new Exception('阿里云存储参数未设置', 1);
        }
        $this->storage = new ALIOSS($config['access_id'], $config['access_key'], $config['end_point']);
        $this->storage->set_enable_domain_style(true);
        $this->bucket_name = $config['bucket_name'];
        $this->options = $config['options'];
        $this->rootPath = isset($config['rootPath']) ? $config['rootPath'] : '';
    }

    /**
     * 检测上传根目录 (阿里云存储会自动创建目录)
     * @param string $rootPath 上传根目录
     * @return bool true-上传成功 false-上传失败
     */
    public function checkRootPath($rootPath)
    {
        $this->rootPath = trim($rootPath, './') . '/';
        return true;
    }

    /**
     * 检测上传目录 (阿里云存储会自动创建目录)
     * @param string $savePath 上传目录
     * @return bool true-通过 false-失败
     */
    public function checkSavePath($savePath)
    {
        return true;
    }

    /**
     * 创建目录 (阿里云存储会自动创建目录)
     * @param string $path  目录
     * @return bool true-创建成功 false-创建失败
     */
    public function mkdir($path)
    {
        return true;
    }

    /**
     * 保存上传文件
     * @param array $file           保存的文件信息
     * @param bool|true $replace    同名文件是否覆盖
     * @return bool                 保存状态，true-成功 false-失败
     */
    public function save($file, $replace = true)
    {
        $result = $this->storage->upload_file_by_file($this->bucket_name, $this->rootPath.$file['savepath'].$file['savename'], $file['tmp_name'], $this->options);
        if((int)($result->status / 100) == 2){
            return true;
        }else{
            $this->error = $result->body;
            return false;
        }
    }

    /**
     * 检测文件是否存在
     * @param $file
     * @return bool
     */
    public function isFileExists($file)
    {
        $result = $this->storage->is_object_exist($this->bucket_name, ltrim($file, '/'), $this->options);
        if($result->status == 200){
            $result = true;
        }else if($result->status == 404){
            $result = false;
        }else{
            $result = false;
        }
        return $result;
    }

}