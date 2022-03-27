<?php

namespace App\Common\File\Storage;

/**
 * Class StorageTrait
 * @package App\Common\File\Storage
 * @author Dyso Deng <dysodengs@gmail.com>
 * @date 2016-01-23
 */
trait StorageTrait {

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError()
    {
        return $this->error;
    }

}