<?php

namespace App\Common\Util;

/**
 * 数据转换工具
 * Class Convert
 * @package App\Common\Util
 */
class Convert
{
    /**
     * 对象转数组
     * @param $data
     * @return array|bool
     */
    public static function objectToArray($data)
    {
        $data = (array)$data;
        foreach ($data as $k => $v) {
            if (gettype($v) == 'resource') return false;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $data[$k] = (array)Convert::objectToArray($v);
        }
        return $data;
    }

    /**
     * 数组转对象
     * @param $data
     * @return bool|object
     */
    public static function arrayToObject($data)
    {
        if (gettype($data) != 'array') return false;
        foreach ($data as $k => $v) {
            if (gettype($v) == 'array' || gettype($v) == 'object')
                $data[$k] = (object)Convert::arrayToObject($v);
        }
        return (object)$data;
    }

    /**
     * 数组转XML
     * @param $data
     * @param string $rootNodeName
     * @param null $xml
     * @return mixed
     */
    public static function arrayToXml($data, $rootNodeName = 'data', $xml = null)
    {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null) {
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }
        $data = Convert::objectToArray($data);
        // loop through the data passed in.
        foreach ($data as $key => $value) {
            // no numeric keys in our xml please!
            //if (is_numeric($key)) {
            // make string key...
            //$key = $xml->getName().'_'.(string)$key;
            //}
            is_numeric($key) && $key = "item id=\"$key\"";

            // replace anything not alpha numeric
            //$key = preg_replace('/[^a-z]/i', '', $key);

            // if there is another array found recrusively call this function
            if (is_array($value)) {
                $node = $xml->addChild($key);
                // recrusive call.
                Convert::arrayToXml($value, $rootNodeName, $node);
            } else {
                // add single node.
                $value = htmlentities($value);
                $xml->addChild($key, $value);
            }

        }
        // pass back as string. or simple xml object if you want!
        return $xml->asXML();
    }

    static public function xmlToArray($xml)
    {

    }

    /**
     * JSON转数组
     * @param $data
     * @return array|mixed
     */
    static public function jsonToArray($data)
    {
        $array = [];
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_json($v)) {
                    $array[$k] = json_decode($v, true);
                } else {
                    $array[$k] = $v;
                }
            }
        } elseif (is_json($data)) {
            $array = json_decode($data, true);
        } else {
            $array = $data;
        }

        return $array;
    }

}