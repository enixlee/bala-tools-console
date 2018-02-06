<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/4/5
 * Time: 下午3:57
 */

namespace ZeusConsole\Commands\miniPay\Utils;


class HexMd5
{
    /**
     * 16位MD5加密
     * @param $value
     * @return string
     */
    public static function md5_16($value)
    {
        return self::md5_32_to_16(md5($value));
    }

    /**
     * md5 32位转换到16为
     * @param $value
     * @return bool|string
     */
    public static function md5_32_to_16($value)
    {
        if (strlen($value) != 32) {
            return false;
        }
        return substr($value, 8, 16);
    }

    /**
     * 16进制转换bigint
     * @param $hex
     * @return string
     */
    public static function hex16ToBigint($hex)
    {
        return gmp_strval(gmp_init($hex, 16));
    }
}