<?php

namespace App\Common\File;

use App\Common\File\Storage\StorageInterface;

/**
 * 文件上传管理器
 * Class Filesystem
 * @package App\Common\File
 * @author Dyso Deng <dysodengs@gmail.com>
 * @date 2016-01-15
 */
class Uploader
{
    /**
     * 默认上传配置
     * @var array
     */
    private $config = array(
        'allowMimeType'         =>  [], //允许上传的Mime类型列表,空为不限制
        'allowImageSize'       =>  '', //允许上传的图片尺寸大小,'width.height',空为不限制
        'allowCapacitySize' =>  0, //允许上传的文件容量大小,0为不限制
        'filePath'      =>  '', //文件路径
        'exts'          =>  array(), //允许上传的文件后缀
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>  './Uploads/', //保存根路径
        'savePath'      =>  '', //保存路径
        'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
        'replace'       =>  false, //存在同名是否覆盖
        'hash'          =>  true, //是否生成hash编码
        'callback'      =>  false, //检测文件是否存在的回调函数，如果存在返回文件信息数组
        'storage'       =>  'file', // 文件存储驱动 File-本地存储 AliOSS-阿里云存储
        'storageConfig' =>  array(), // 上传驱动配置
        'storage_type'  =>  '',
    );

    protected $storage = null;//文件存储驱动

    protected $error = '';//错误消息
    protected $rootPath;
    protected $savePath;

    /**
     * 构造函数
     * @param StorageInterface $storage 文件存储器
     * @param array $config             配置列表
     */
    public function __construct(StorageInterface $storage, $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->storage = $storage;
    }

    /**
     * 上传文件
     * @param string $files
     * @return array|bool
     */
    public function upload($files = '')
    {
        if($files == ''){
            $files = $_FILES;
        }

        if(empty($files)){
            $this->error = '没有上传的文件！';
            return false;
        }

        if(!$this->storage->checkRootPath($this->config['rootPath'])){
            $this->error = $this->storage->getError();
            return false;
        }

        if(!$this->storage->checkSavePath($this->config['savePath'])){
            $this->error = $this->storage->getError();
            return false;
        }

        /* 逐个检测并上传文件 */
        $info    =  array();
        if(function_exists('finfo_open')){
            $finfo   =  finfo_open(FILEINFO_MIME_TYPE);
        }

        $files = $this->dealFiles($files);

        foreach($files as $key=>$file){
            $file['name']  = strip_tags($file['name']);
            if(!isset($file['key']))   $file['key']    =   $key;
            /* 通过扩展获取文件类型，可解决FLASH上传$FILES数组返回文件类型错误的问题 */
            if(isset($finfo)){
                $file['type']   =   finfo_file($finfo, $file['tmp_name']);
            }

            /* 获取上传文件后缀，允许上传无后缀文件 */
            $file['ext']    =   pathinfo($file['name'], PATHINFO_EXTENSION);

            /* 文件上传检测 */
            if (!$this->check($file)){
                continue;
            }

            /* 获取文件hash */
            if($this->config['hash']){
                $file['md5']  = md5_file($file['tmp_name']);
                $file['sha1'] = sha1_file($file['tmp_name']);
            }

            /* 调用回调函数检测文件是否存在 */
            $data = call_user_func($this->config['callback'], $file);
            if( $this->config['callback'] && $data ){
                if($this->storage->isFileExists($data['fullpath'])){
                    $info[$key] = $data;
                    continue;
                }else if(isset($this->config['removeTrash']) && $this->config['removeTrash']){
                    call_user_func($this->config['removeTrash'], $data);//删除垃圾数据
                }
            }

            /* 生成保存文件名 */
            $savename = $this->getSaveName($file);
            if(false == $savename){
                continue;
            } else {
                $file['savename'] = $savename;
            }

            /* 检测并创建子目录 */
            $subpath = $this->getSubPath($file['name']);
            if(false === $subpath){
                continue;
            } else {
                $file['savepath'] = $this->config['savePath'] . $subpath;
            }

            /* 对图像文件进行严格检测 */
            $ext = strtolower($file['ext']);
            if(in_array($ext, array('gif','jpg','jpeg','bmp','png','swf'))) {
                $imginfo = getimagesize($file['tmp_name']);
                if(empty($imginfo) || ($ext == 'gif' && empty($imginfo['bits']))){
                    $this->error = '非法图像文件！';
                    continue;
                }
            }

            /* 保存文件 并记录保存成功的文件 */
            if ($this->storage->save($file,$this->config['replace'])) {
                unset($file['error'], $file['tmp_name']);
                $info[$key] = $file;
            } else {
                $this->error = $this->storage->getError();
            }
        }
        if(isset($finfo)){
            finfo_close($finfo);
        }
        return empty($info) ? false : $info;
    }

    /**
     * 下载文件
     */
    public function download(){}

    /**
     * 允许上传的mime类型
     * @param $mime
     * @param string $ext
     * @return bool
     */
    protected function allowMimeType($mime, $ext = '')
    {
        if(empty($this->config['allowMimeType'])){
            return true;
        }else{
            $key = MimeTypes::get_mime_key_by_mime($mime);
            if($key){
                if($ext == 'mp3' && $mime == 'audio/mpeg'){
                    $key = ['mp3'];
                }
                if($this->config['storage_type'] == 'panel' && ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg')){
                    return true;
                }
                foreach($key as $filetype){
                    if(in_array($filetype, $this->config['allowMimeType'])){
                        return true;
                    }
                }
                return false;
            }
            return false;
        }
    }

    /**
     * 允许上传的图片尺寸大小(像素)
     * @param $file
     * @return bool
     */
    protected function allowImageSize($file)
    {
        $info = getimagesize($file);
        $width = $info[0];
        $height = $info[1];
        if($this->config['allowImageSize'] === ''){
            return true;
        }
        $size = explode('.', $this->config['allowImageSize']);


        if($width == $size[0] && $height == $size[1]){ //图片尺寸相同直接
            return true;
        }else{
            //等比例图片可允许上传
            try {
                if (($width / $height) == ($size[0] / $size[1])) {
                    return true;
                }
            }catch (\Exception $exception){ //防止除0
                return false;
            }
        }
        return false;
    }

    /**
     * 允许上传的文件容量大小(B,KB,MB,GB,TB)
     * @param $size
     * @return bool
     */
    protected function allowCapacitySize($size)
    {
        return !($size > $this->config['allowCapacitySize']) || (0 == $this->config['allowCapacitySize']);
    }

    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function dealFiles($files)
    {
        $fileArray  = array();
        $n          = 0;
        foreach ($files as $key=>$file){
            if(is_array($file['name'])) {
                $keys       =   array_keys($file);
                $count      =   count($file['name']);
                for ($i=0; $i<$count; $i++) {
                    $fileArray[$n]['key'] = $key;
                    foreach ($keys as $_key){
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            }else{
                $fileArray = $files;
                break;
            }
        }
        return $fileArray;
    }

    /**
     * 检查上传的文件
     * @param array $file 文件信息
     * @return bool
     */
    private function check($file)
    {
        /* 文件上传失败，捕获错误代码 */
        if ($file['error']) {
            $this->error($file['error']);
            return false;
        }

        /* 无效上传 */
        if (empty($file['name'])){
            $this->error = '未知上传错误！';
        }

        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error = '非法上传文件！';
            return false;
        }

        /* 检查文件大小 */
        if (!$this->allowCapacitySize($file['size'])) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        /* 检查图片尺寸 */
        if(!$this->allowImageSize($file['tmp_name'])){
            $this->error = '图片尺寸不符';
            return false;
        }

        /* 检查文件Mime类型 */
        //TODO:FLASH上传的文件获取到的mime类型都为application/octet-stream
        if (!$this->allowMimeType($file['type'], $file['ext'])) {
            $this->error = '上传文件MIME类型不允许！'.$file['type'];
            return false;
        }

        /* 检查文件后缀 */
        if (!$this->checkExt($file['ext'])) {
            $this->error = '上传文件后缀不允许';
            return false;
        }

        /* 通过检测 */
        return true;
    }

    /**
     * 检查上传的文件后缀是否合法
     * @param string $ext 后缀
     * @return bool
     */
    private function checkExt($ext)
    {
        return empty($this->config['exts']) ? true : in_array(strtolower($ext), $this->config['exts']);
    }

    /**
     * 根据上传文件命名规则取得保存文件名
     * @param mixed $file 文件信息
     * @return bool|string
     */
    private function getSaveName($file)
    {
        $rule = $this->config['saveName'];
        if (empty($rule)) { //保持文件名不变
            /* 解决pathinfo中文文件名BUG */
            $filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);
            $saveName = $filename;
        } else {
            $saveName = $this->getName($rule, $file['name']);
            if(empty($saveName)){
                $this->error = '文件命名规则错误！';
                return false;
            }
        }

        /* 文件保存后缀，支持强制更改文件后缀 */
        $ext = empty($this->config['saveExt']) ? $file['ext'] : $this->config['saveExt'];

        return $saveName . '.' . $ext;
    }

    /**
     * 获取子目录的名称
     * @param string $filename  上传的文件信息
     * @return bool|string
     */
    private function getSubPath($filename)
    {
        $subPath = '';
        $rule    = $this->config['subName'];
        if ($this->config['autoSub'] && !empty($rule)) {
            $subPath = $this->getName($rule, $filename) . '/';

            if(!empty($subPath) && !$this->storage->mkdir($this->config['savePath'] . $subPath)){
                $this->error = $this->storage->getError();
                return false;
            }
        }
        return $subPath;
    }

    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule, $filename)
    {
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            foreach ($param as &$value) {
                $value = str_replace('__FILE__', $filename, $value);
            }
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }

    /**
     * 获取错误代码信息
     * @param string $errorNo  错误号
     */
    private function error($errorNo)
    {
        switch ($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！';
                break;
            case 3:
                $this->error = '文件只有部分被上传！';
                break;
            case 4:
                $this->error = '没有文件被上传！';
                break;
            case 6:
                $this->error = '找不到临时文件夹！';
                break;
            case 7:
                $this->error = '文件写入失败！';
                break;
            default:
                $this->error = '未知上传错误！';
        }
    }

    /**
     * 获取最后一条错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

}