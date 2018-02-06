<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/6/17
 * Time: 下午3:45
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2;

/**
 * Class RpcGenerateErrorCode
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2
 */
class RpcGenerateErrorCode
{
    //- { name: CELLPHONE_NOT_EXISTS, code: 100, comment: "用户手机号不存在" }
    private $name;
    private $code;
    private $comment;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}