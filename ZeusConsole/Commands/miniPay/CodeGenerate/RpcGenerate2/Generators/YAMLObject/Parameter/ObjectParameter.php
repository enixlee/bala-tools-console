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
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\RpcOutputParameter;

class ObjectParameter extends ParameterBase
{
    /**
     * @var boolean
     */
    private $repeated;

    /**
     * @return bool
     */
    public function isRepeated(): bool
    {
        return $this->repeated;
    }

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

    protected const OBJ_PREFIX = "obj.";

    /**
     * @return bool
     */
    public function isObject()
    {
        return starts_with($this->getType(), self::OBJ_PREFIX);
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
        $this->repeated = boolval($this->originData['repeated'] ?? false);//  boolval($this->originData['repeated']) : false;
    }


    public function getTypeDeclareAsString()
    {
        $declare = parent::getTypeDeclareAsString();
        if ($this->isObject()) {
            $declare = str_replace_first(self::OBJ_PREFIX, "", $this->getType());
//            if ($this->isRepeated()) {
//                $declare = "array";
//            }
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