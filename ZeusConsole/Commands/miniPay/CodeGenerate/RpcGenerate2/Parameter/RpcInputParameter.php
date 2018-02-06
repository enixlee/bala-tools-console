<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/6/26
 * Time: 下午5:54
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter;

use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError;

/**
 * RPC调用参数
 * Class RpcInputParameter
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter
 */
class RpcInputParameter extends ParameterBase
{
    /**
     * 获取变量声明类型
     * @return string
     */
    protected function getTypeAlias()
    {
        return $this->getTypeTemplateConfig()->getTypeDeclareAsString($this->getType());
    }


    public function getParameterDocument()
    {
        return "* @param " . $this->getTypeAlias() . " " . $this->getParameterVarString() . ($this->isRequire() ? "" : " |null") . " " . $this->getType() . " " . $this->getComment();
    }

    public function getParameterAsArrayKeyDescription()
    {
        return "//" . $this->getComment();
    }

    public function getParameterAsArrayKey()
    {
        return '"' . strtolower($this->getName()) . '" => ' . ($this->isRequire() ? "true" : "null");
    }

    public function getParameterAsArrayKeyWithParameterVar()
    {
        return '"' . strtolower($this->getName()) . '" => ' . $this->getParameterVarString();
    }

    /**
     * 获取函数声明字段
     * @return string
     */
    public function getParameterDeclareString()
    {
        $declare = $this->getTypeAlias() . " " . $this->getParameterVarString();

        if ($this->isRequire()) {
            if (!is_null($this->getDefault())) {
                $declare .= " = " . $this->echoDefaultValue();
            }
        } else {
            $declare .= " = " . $this->echoDefaultValue();
        }
        return $declare;
    }

    /**
     * 输出默认值
     * @return string
     */
    protected function echoDefaultValue()
    {
        if ($this->getType() == "string") {
            $value = (is_null($this->getDefault()) ? "null" : '"' . strval($this->getDefault()) . '"');
        } else {
            $value = (is_null($this->getDefault()) ? "null" : strval($this->getDefault()));
        }

        return $value;
    }

    /**
     * 获取变量字符串
     * @return string
     */
    public function getParameterVarString()
    {
        return "$" . $this->getName();
    }

    /**
     * 获取参数类型检测函数
     * @return string
     */
    public function getParameterTypeCheckString()
    {
        $typeName = strtolower($this->getType());
        if (!is_null($this->getChoice())) {
            if ($typeName == "json") {
                $typeName = "json_choice";
            } else {
                $typeName = "choice";
            }
        }
        $typeCheckTemplate = $this->getTypeTemplateConfig()->getTypeCheckFunctionTemplate($typeName);


        return translator()->trans($typeCheckTemplate, [
                "{{ value }}" => $this->getParameterVarString(),
                "{{ min }}" => is_null($this->getMin()) ? "null" : strval($this->getMin()),
                "{{ max }}" => is_null($this->getMax()) ? "null" : strval($this->getMax()),
                "{{ bigIntMin }}" => is_null($this->getMin()) ? "null" : '"' . strval($this->getMin()) . '"',
                "{{ bigIntMax }}" => is_null($this->getMax()) ? "null" : '"' . strval($this->getMax()) . '"',
                "{{ nullEnable }}" => $this->isRequire() ? "false" : "true",
                "{{ choices }}" => is_null($this->getChoice()) ? "null" : "[" . join(", ", $this->getChoice()) . "]"
            ]) . ";";
    }


    public function fromArray(Array $array)
    {
        $this->fillDatas($array);
        if (!$this->getTypeTemplateConfig()->hasTypeName($this->getType())) {
            throw new RpcGenerateParserError($this->getName());
        }

    }

}