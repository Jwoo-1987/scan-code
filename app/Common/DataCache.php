<?php

namespace App\Common;

use Illuminate\Support\Facades\Cache;


class DataCache
{
    /**
     * 重新封装了Cache::get方法，增加了缓存锁的判断
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $key_lock = Cache::get($key.'_lock');
        //缓存锁
        if($key_lock){
            $time = 0;
            while(true){
                $key_lock = Cache::get($key.'_lock');
                if(!$key_lock){
                    break;
                }
                sleep(1);//睡一秒
                $time++;
                if($time >= 10){
                    //10秒后退出，可能是由于前面的请求出了异常，由本次请求再次去保存缓存
                    return false;
                }
            }
        }
        $cache = Cache::get($key, $default);
        return $cache;
    }

    /**
     * 设置缓存锁
     * @param string $key
     * @param int $value  0-解锁 1-加锁
     * @return mixed
     */
    public static function setCacheLock($key, $value = 0)
    {
        if($value == 0){
            return Cache::forget($key.'_lock'); //删除缓存锁
        }else {
            return Cache::forever($key . '_lock', $value);//永久保存缓存锁
        }
    }

    /**
     * 重新封装的Cache::forget方法
     * @param $key
     */
    public static function forget($key)
    {
        Cache::forget($key);
        Cache::forget($key.'_lock');
    }
}