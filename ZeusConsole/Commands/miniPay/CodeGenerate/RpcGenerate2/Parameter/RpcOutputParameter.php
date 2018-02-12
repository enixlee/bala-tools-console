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
//        $this->functionName = ucfirst($functionName);
    }


    /**
     * 是否是自定义消息
     * @return bool
     */
    public function isMessage()
    {
        return $this->getType() == 'message';
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
        $this->repeated = isset($this->originData['repeated']) ? boolval($this->originData['repeated']) : false;
        if ($this->isMessage()) {
            $this->fillMessageData($this->originData['messageData']);
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

    public function getTypeDeclareAsString()
    {
        $declare = parent::getTypeDeclareAsString();
        if ($this->isRepeated()) {
            if ($this->isMessage()) {
                $declare = $this->getMessageClassName() . "[]";
            } else {
                $declare = "array";
            }
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
            } else {
                $declare = $declare . "[]";
            }
        } elseif ($this->isMessage()) {
            $declare = $this->getMessageClassName();
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