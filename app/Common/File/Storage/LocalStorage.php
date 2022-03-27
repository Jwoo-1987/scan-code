<?php

namespace App\Common\File\Storage;

/**
 * 本地文件存储器
 * Class Local
 * @package App\Common\File\Storage
 * @author Dyso Deng <dysodengs@gmail.com>
 * @date 2016-01-15
 */
class LocalStorage implements StorageInterface
{
    use StorageTrait;

    protected $root = '';
    protected $rootPath = '';
    protected $error = '';

    /**
     * 构架函数，用于设置存储配置
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->rootPath = isset($config['rootPath']) ? $config['rootPath'] : '';
        $root = isset($config['root']) ? $config['root'] : '';
        $this->root = app('path.public').$root;
    }

    /**
     * 检测上传根目录
     * @param string $rootPath 上传根目录
     * @return bool true-上传成功 false-上传失败
     */
    public function checkRootPath($rootPath)
    {
        if(!is_dir($this->root.$rootPath)){
            if(!is_writable($this->root)){
                $this->error = '上传根目录不可写！';
                return false;
            }else{
                try{
                    mkdir($this->root.$rootPath, 0777, true);
                }catch (\Exception $e){
                    $this->error = '上传根目录创建失败！请尝试手动创建:'.$rootPath;
                    return false;
                }
            }
        }
        $this->rootPath = trim($rootPath, './') . '/';
        return true;
    }

    /**
     * 检测上传目录
     * @param string $savePath 上传目录
     * @return bool true-通过 false-失败
     */
    public function checkSavePath($savePath)
    {
        if (!$this->mkdir($savePath)) {
            return false;
        } else {
            if (!is_writable($this->root.$this->rootPath . $savePath)) {
                $this->error = '上传目录 ' . $savePath . ' 不可写！';
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 创建目录
     * @param string $path  目录
     * @return bool true-创建成功 false-创建失败
     */
    public function mkdir($path)
    {
        $dir = $this->root.$this->rootPath . $path;
        if(is_dir($dir)){
            return true;
        }

        if(mkdir($dir, 0777, true)){
            return true;
        } else {
            $this->error = "目录 {$path} 创建失败！";
            return false;
        }
    }

    /**
     * 保存上传文件
     * @param array $file           保存的文件信息
     * @param bool|true $replace    同名文件是否覆盖
     * @return bool                 保存状态，true-成功 false-失败
     */
    public function save($file, $replace = true)
    {
        $filename = $this->root.$this->rootPath . $file['savepath'] . $file['savename'];

        if (!$replace && is_file($filename)) {
            $this->error = '存在同名文件' . $file['savename'];
            return false;
        }

        if (!move_uploaded_file($file['tmp_name'], $filename)) {
            $this->error = '文件上传保存错误！';
            return false;
        }

        return true;
    }

    /**
     * 检测文件是否存在
     * @param $file
     * @return mixed
     */
    public function isFileExists($file)
    {
        return file_exists($this->root.$file);
    }

}