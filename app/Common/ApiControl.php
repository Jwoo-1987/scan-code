<?php

namespace App\Common;

/**
 * Class ApiContrl
 * @package App\Common\Api
 */
class ApiControl
{
    /**
     * 执行API请求
     * @param string $url       API地址
     * @param array $data       请求数据
     * @param string $method    请求方法 POST GET PUT PATCH DELETE
     * @param string $type      接口类型 panel-平台api
     * @param string $version   接口版本
     * @return mixed
     */
    public static function getApiUrl($url, $data = array(), $method = 'get', $type = 'panel', $version = 'v1')
    {
        switch ($type){
            case 'panel':
                $api_url = url('api').'/'.$version.'/';
                break;
            case 'auth':
                $api_url = url('auth').'/';
                break;
            default :
                $api_url = url('api').'/'.$version.'/';
        }

        $api_url .= trim($url, '/');
        $method = strtolower($method);
        $func = 'http_'.$method;
        $data['time'] = time();
        $buildData = static::buildData($data, $method, $type);
        if($method == 'get'){
            $api_url .= '?'.$buildData;
        }
        $result = static::$func($api_url, $buildData);
        return static::decResult($result);
    }

    /**
     * 请求数据构造
     * @param array $data
     * @param string $method
     * @param string $type
     * @return array|string
     */
    protected static function buildData(array $data, $method = 'get', $type = 'panel')
    {
        $app_key = self::getAppKey();

        if($method == 'get'){
            if($type != 'auth') {
                $sign = static::getSignature($data, $app_key);
                $data['api_key'] = $app_key;
                $data['sign'] = $sign;
            }
            $aGET = array();
            foreach($data as $key=>$val) {
                $aGET[] = $key."=".urlencode($val);
            }
            $resultData = join("&", $aGET);
        }else{
            if($type != 'auth') {
                $sign = static::getSignature($data, $app_key);
                $resultData = array(
                    'api_key' => $app_key,
                    'sign' => $sign,
                );
            }else{
                $resultData = [];
            }
            foreach($data as $key=>$val){
                $resultData[$key] = $val;
            }
        }

        return $resultData;
    }

    /**
     * 解返回数据
     * @param $json
     * @return mixed
     */
    protected static function decResult($json)
    {
        $json = json_decode($json, true);
        return $json;
    }

    /**
     * @param $url
     * @param $param
     * @param bool $post_file
     * @param array $header
     * @return bool|mixed
     */
    public static function http_post($url, $param, $post_file = false, $header = [])
    {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt ( $oCurl, CURLOPT_SSLVERSION, 1 ); // CURL_SSLVERSION_TLSv1
        }
        if (is_string ( $param ) || $post_file) {
            $strPOST = $param;
        } else {
            $strPOST = http_build_query($param);
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $oCurl, CURLOPT_POST, true );
        curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    public static function http_get($url)
    {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSLVERSION, 1 ); // CURL_SSLVERSION_TLSv1
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * 签名生成算法
     * @param  array  $params API调用的请求参数集合的关联数组，不包含sign参数
     * @param  string $secret 签名的密钥即获取access token时返回的session secret
     * @return string 返回参数签名值
     */
    public static function getSignature(array $params, $secret)
    {
        $str = '';  //待签名字符串
        $str .= $secret; //将签名密钥拼接到签名字符串最前面
        //先将参数以其参数名的字典序升序进行排序
        ksort($params);
        //遍历排序后的参数数组中的每一个key/value对
        foreach ($params as $k => $v) {
            //为key/value对生成一个key=value格式的字符串，并拼接到待签名字符串后面
            $str .= "$k=$v";
        }
        //将签名密钥拼接到签名字符串最后面
        $str .= $secret;
        //通过md5算法为签名字符串生成一个md5签名，该签名就是我们要追加的sign参数值
        return md5($str);
    }

    /**
     * 获取APP_KEY
     * @return mixed
     */
    public static function getAppKey()
    {
        return env('APP_KEY', '');
    }

}