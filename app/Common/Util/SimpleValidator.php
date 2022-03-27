<?php

namespace App\Common\Util;

/**
 * 简单数据合法性验证
 * Class SimpleValidator
 * @package App\Common\Util
 */
class SimpleValidator
{
    /**
     * 邮箱验证
     * @param $value
     * @return bool
     */
    public static function email($value)
    {
        $value = (string) $value;
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL);
        return $valid !== false ;
    }

    /**
     * 用户昵称验证
     * @param $value
     * @param array $option
     * @return bool
     */
    public static function nickname($value, array $option = array())
    {

        $option = array_merge(
            array('minLength' => 4, 'maxLength' => 18),
            $option
        );

        $len = (strlen($value) + mb_strlen($value, 'utf-8')) / 2;
        if ($len > $option['maxLength'] || $len < $option['minLength']) {
            return false;
        }
        return !!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z0-9_.]+$/u', $value);
    }

    /**
     * 密码强度验证
     * @param $value
     * @param array $option
     * @return bool
     */
    public static function password($value, array $option = array())
    {
        return !!preg_match('/^[\S]{5,20}$/u', $value);
    }

    public static function truename($value)
    {
        return !!preg_match('/^[\x{4e00}-\x{9fa5}]{2,5}$/u', $value);
    }

    /**
     * 身份证验证
     * @param $value
     * @return bool
     */
    public static function idCard($value)
    {
        return !!preg_match('/^\d{17}[0-9xX]$/', $value);
    }

    /**
     * 银行卡验证
     * @param $value
     * @return bool
     */
    public static function bankCardId($value)
    {
        return !!preg_match('/^(\d{16}|\d{17}|\d{18}|\d{19})$/', $value);
    }

    /**
     * 手机号验证
     * @param $value
     * @return bool
     */
    public static function mobile($value)
    {
        return !!preg_match('/^1\d{10}$/', $value);
    }

    /**
     * 固定电话号码验证
     * @param $value
     * @return bool
     */
    public static function phone($value)
    {
        return !!preg_match('/^(\d{4}-|\d{3}-)?(\d{8}|\d{7})$/', $value);
    }

    /**
     * 400固话验证
     * @param $value
     * @return bool
     */
    public static function phone400($value)
    {
        return !!preg_match('/^400(-\d{3,4}){2}$/', $value);
    }

    /**
     * 日期验证
     * @param $value
     * @return bool
     */
    public static function date($value)
    {
        return !!preg_match('/^(\d{4}|\d{2})-((0?([1-9]))|(1[0-2]))-((0?[1-9])|([12]([0-9]))|(3[0|1]))$/', $value);
    }

    /**
     * QQ号验证
     * @param $value
     * @return bool
     */
    public static function qq($value)
    {
        return !!preg_match('/^[1-9]\d{4,}$/', $value);
    }

    /**
     * 整数验证
     * @param $value
     * @return bool
     */
    public static function integer($value)
    {
        return !!preg_match('/^[+-]?\d{1,9}$/', $value);
    }

    /**
     * 浮点数验证
     * @param $value
     * @return bool
     */
    public static function float($value)
    {
        return !!preg_match('/^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i', $value);
    }

    /**
     * 日期时间验证
     * @param $value
     * @return bool
     */
    public static function dateTime($value)
    {
        return !!preg_match('/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/', $value);
    }

    /**
     * 网址URL验证
     * @param $value
     * @return bool
     */
    public static function site($value)
    {
        return !!preg_match('/^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/', $value);
    }

}