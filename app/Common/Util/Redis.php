<?php

namespace App\Common\Util;

class Redis
{
    /**
     * Redis Instance
     * @var Redis 
     */
    protected static $_instance = null;

    /**
     * Construct
     */
    private function __construct() {}
    
    private function __clone() {}

    /**
     * @param array $config
     * @return Redis|\Redis
     */
    public static function getInstance(array $config)
    {
        if(self::$_instance != null && self::$_instance instanceof \Redis){
            if(isset($config['database'])){
                self::$_instance->select($config['database']);
            }
            return self::$_instance;
        }

        if(!isset($config['port'])){
            $config['port'] = 6379;
        }
        if(!isset($config['database'])){
            $config['database'] = 1;
        }
        $db['database'] = $config['database'];
        unset($config['database']);
        self::$_instance = new \Redis();
        self::$_instance->connect($config['host'], $config['port']);
        if(isset($config['auth']) && $config['auth']) {
            self::$_instance->auth($config['auth']);
        }
        //self::$_instance->select($db['database']);
        return self::$_instance;
    }
}
