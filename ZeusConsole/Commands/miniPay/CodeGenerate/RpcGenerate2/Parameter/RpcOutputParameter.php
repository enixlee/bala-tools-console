<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/6/26
 * Time: 下午7:52
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Tests\NameConverter\CamelCaseToSnakeCaseNameConverterTest;

/**
 * RPC返回值类
 * Class RpcOutputParameter
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter
 */
class RpcOutputParameter extends ParameterBase
{
    /**
     * 自定义消息基础类名,就是类的前置名称
     * @var
     */
    private $messageBaseClassName;

    /**
     * @return mixed
     */
    public function getMessageBaseClassName()
    {
        return $this->messageBaseClassName;
    }

    /**
     * @param mixed $messageBaseClassName
     */
    public function setMessageBaseClassName($messageBaseClassName)
    {
        $this->messageBaseClassName = $messageBaseClassName;
    }


    /**
     * 自定义消息类名
     * @var string
     */
    private $messageClassName = "";

    /**
     * @return string
     */
    public function getMessageClassName(): string
    {
        return $this->messageClassName;
    }

    /**
     * @param string $messageClassName
     */
    protected function setMessageClassName(string $messageClassName)
    {
        $messageClassName = ucfirst($messageClassName);
        if (is_null($this->parentParameter)) {
            $this->messageClassName = $this->messageBaseClassName . $messageClassName;

        } else {
            $this->messageClassName = $this->parentParameter->getMessageClassName() . $messageClassName;
        }
    }

    /**
     * 父参数
     * @var RpcOutputParameter
     */
    private $parentParameter;

    /**
     * @param RpcOutputParameter $parentParameter
     */
    protected function setParentParameter(RpcOutputParameter $parentParameter)
    {
        $this->parentParameter = $parentParameter;
    }


    /**
     * @var RpcOutputParameter[]
     */
    private $messageData = [];

    /**
     * @return RpcOutputParameter[]
     */
    public function getMessageData()
    {
        return $this->messageData;
    }


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
     * @var string
     */
    private $functionName;

    /**
     * @param $originDataArray
     * @throws \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError
     */
    public function fillDatas($originDataArray)
    {
        parent::fillDatas($originDataArray);

        $this->setFunctionName($this->getName());
        if ($this->isMessage()) {
            $this->fillMessageData($originDataArray['messageData']);
        }
    }

    /**
     * @param array $messageData
     * @throws \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError
     */
    protected function fillMessageData(array $messageData)
    {
        //设置类名
        $this->setMessageClassName($this->getName());

        foreach ($messageData as $messageDatum) {
            $parameter = new RpcOutputParameter($this->typeTemplateConfig);
            $parameter->setParentParameter($this);
            $parameter->fillDatas($messageDatum);
            $this->messageData[] = $parameter;
        }

    }

    /**
     * @return string
     */
    public function getObjectFullClassName()
    {
        $nameSpace = getConfig('miniPay.codeGenerate.rpcGenerate2.NameSpace', "bala\codeTemplate");
        return "\\" . $nameSpace . "\\objects\\" . $this->getObjectTypeClassName();
    }


    public function getTypeDeclareAsString()
    {
        $declare = parent::getTypeDeclareAsString();
        if ($this->isRepeated()) {
            if ($this->isMessage()) {
                $declare = $this->getMessageClassName() . "[]";
            } elseif ($this->isObject()) {
                $declare = $this->getObjectFullClassName() . "[]";
            } else {
                $declare = "array";
            }
        } elseif ($this->isObject()) {
            $declare = $this->getObjectFullClassName();
        } elseif ($this->isMessage()) {
            $declare = $this->getMessageClassName();
        } elseif ($declare == "mixed") {
            $declare = "";
        }
        return $declare;
    }

    public function getVariableCommentString()
    {
        $declare = parent::getTypeDeclareAsString();
        if ($this->isRepeated()) {
            if ($this->isMessage()) {
                $declare = $this->getMessageClassName() . "[]";
            } elseif ($this->isObject()) {
                $declare = $this->getObjectFullClassName() . "[]";
            } else {
                $declare = $declare . "[]";
            }
        } elseif ($this->isMessage()) {
            $declare = $this->getMessageClassName();
        } elseif ($this->isObject()) {
            $declare = $this->getObjectFullClassName();
        }
        return $declare;
    }

    /**
     * 获取原始的类型说明
     * @return string
     */
    public function getOriginTypeDeclareAsString()
    {
        if ($this->isMessage()) {
            $declare = $this->getMessageClassName();
        } else {
            $declare = parent::getTypeDeclareAsString();
        }
        return $declare;
    }


}