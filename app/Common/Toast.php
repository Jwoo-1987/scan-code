<?php

namespace App\Common;

use App\Common\Util\Convert;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


class Toast
{
    /**
     * 接口返回数据格式
     * @var array
     */
    protected static $dataType = array(
        'json',
        'xml'
    );

    /**
     * 返回正确的数据
     * @param $data
     * @param string $redirect      跳转URL
     * @param string $type
     * @param int $httpStatusCode
     * @return mixed
     */
    public static function success($data, $redirect = '', $type = 'json', $httpStatusCode = 200)
    {
        return self::message($data, 0, 'ok', $redirect, $type, $httpStatusCode);
    }

    /**
     * 返回错误的消息数据
     * @param $data
     * @param string $redirect      跳转URL
     * @param int $code
     * @param string $type
     * @param int $httpStatusCode
     * @return mixed
     */
    public static function error($data, $redirect = '', $code = 1, $type = 'json', $httpStatusCode = 200)
    {
        if($code == 0){
            $code = 1;
        }
        return self::message($data, $code, $data, $redirect, $type, $httpStatusCode);
    }

    /**
     * API数据返回，无论如何都是json或xml格式
     * @param $data
     * @param int $code
     * @param string $type
     * @param int $httpStatusCode
     * @return mixed
     */
    public static function api($data, $code = 0, $type = 'json', $httpStatusCode = 200)
    {
        $responseData = array();

        if($code == 0){
            $responseData['status'] = 1;
            $responseData['data'] = $data;
            $responseData['msg'] = 'ok';
        }else{
            $responseData['status'] = 0;
            $responseData['data'] = '';
            $responseData['msg'] = $data;
        }

        $responseData['code'] = $code;

        $function = 'send'.ucfirst(strtolower($type));
        return self::$function($responseData, $httpStatusCode);
    }

    /**
     * 消息返回
     * @param $data
     * @param int $code
     * @param string $errMsg
     * @param string $redirect      跳转URL
     * @param string $type
     * @param int $httpStatusCode
     * @return mixed
     */
    public static function message($data, $code = 0, $errMsg = '', $redirect = '', $type = 'json', $httpStatusCode = 200)
    {
        if(!in_array($type, self::$dataType)){
            $type = 'json';
        }

        $responseData = array();

        if($code == 0){
            $responseData['status'] = 1;
            $responseData['data'] = $data;
        }else{
            $responseData['status'] = 0;
            $responseData['data'] = '';
        }

        $responseData['code'] = $code;
        $responseData['msg'] = $errMsg ?: 'ok';
        if($redirect){
            $responseData['redirect'] = $redirect;
        }

        if(!\request()->ajax()){
            $type = 'toast';
        }

        $function = 'send'.ucfirst(strtolower($type));
        return self::$function($responseData, $httpStatusCode);
    }

    /**
     * 返回JSON数据
     * @param array $data           数据
     * @param int $httpStatusCode   HTTP CODE
     * @return \Illuminate\Http\JsonResponse
     */
    protected static function sendJson(array $data, $httpStatusCode = 200)
    {
        return response()->json($data, $httpStatusCode);
    }

    /**
     * 返回XML数据
     * @param array $data
     * @param int $httpStatusCode
     * @return Response
     */
    protected static function sendXml(array $data, $httpStatusCode = 200)
    {
        return (new Response(Convert::arrayToXml($data, 'root'), $httpStatusCode, array('Content-Type' => 'text/xml')));
    }

    /**
     * 返回提示页面
     * @param array $data
     * @param int $httpStatusCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected static function sendToast(array $data, $httpStatusCode = 200)
    {
        if(Auth::check()){
            $admin_user = Auth::user();
        }else{
            $admin_user = [
                'realname'  =>  '',
                'name'      =>  ''
            ];
        }
        return response()->view('errors.toast', compact('data', 'admin_user'))->setStatusCode($httpStatusCode);
    }

}