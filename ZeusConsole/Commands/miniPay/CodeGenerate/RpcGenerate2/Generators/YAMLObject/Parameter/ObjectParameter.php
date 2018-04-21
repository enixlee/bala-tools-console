<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 6:13 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\Parameter;


use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\ParameterBase;

class ObjectParameter extends ParameterBase
{


    /**
     * @var string
     */
    private $functionName;

    /**
     * @return string
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }


    /**
     * @param string $functionName
     */
    public function setFunctionName($functionName)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter(null, false);
        $this->functionName = $converter->denormalize($functionName);
    }

    /**
     * @param $originDataArray
     * @throws \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError
     */
    public function fillDatas($originDataArray)
    {
        parent::fillDatas($originDataArray);

        $this->setFunctionName($this->getName());
    }

    /**
     * @throws \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError
     */
    public function checkError()
    {
        parent::checkError();
        if ($this->isMessage()) {
            $this->error("Object对象中,字段{$this->getName()},类型不能为message");
        }
    }


    public function getTypeDeclareAsString()
    {
        $declare = parent::getTypeDeclareAsString();
        if ($this->isObject()) {
            $declare = $this->getObjectTypeClassName();
        } else {
            if ($this->isRepeated()) {
                $declare = "array";
            } elseif ($declare == "mixed") {
                $declare = "";
            }
        }

        return $declare;
    }

    public function getVariableCommentString()
    {
        $declare = $this->getTypeDeclareAsString();
        if ($this->isRepeated()) {
            $declare = $declare . "[]";
        }
        return $declare;
    }

    /**
     * 获取原始的类型说明
     * @return string
     */
    public function getOriginTypeDeclareAsString()
    {
        $declare = parent::getTypeDeclareAsString();
        return $declare;
    }

}