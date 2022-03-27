<?php

namespace App\Common\File\Storage;

/**
 * 文件存储器接口
 * 所有文件存储器必须实现该接口
 * Interface StorageInterface
 * @package App\Common\File\Storage
 * @author Dyso Deng <dysodengs@gmail.com>
 * @date 2016-01-15
 */
interface StorageInterface
{
    /**
     * 构架函数，用于设置存储配置
     * @param array $config
     */
    public function __construct($config = []);

    /**
     * 检测上传根目录
     * @param string $rootPath 上传根目录
     * @return bool true-上传成功 false-上传失败
     */
    public function checkRootPath($rootPath);

    /**
     * 检测上传目录
     * @param string $savePath 上传目录
     * @return bool true-通过 false-失败
     */
    public function checkSavePath($savePath);

    /**
     * 创建目录
     * @param string $path  目录
     * @return bool true-创建成功 false-创建失败
     */
    public function mkdir($path);

    /**
     * 保存上传文件
     * @param array $file           保存的文件信息
     * @param bool|true $replace    同名文件是否覆盖
     * @return bool                 保存状态，true-成功 false-失败
     */
    public function save($file, $replace = true);

    /**
     * 检测文件是否存在
     * @param $file
     * @return mixed
     */
    public function isFileExists($file);

}