<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/12/12
 * Time: 下午5:29
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\RpcOutputParameter;

class LogicTemplatesReturnParameterSetterAndGetterWriter extends WriterBase
{

    private $param;

    /**
     * LogicTemplatesReturnParameterSetterAndGetterWriter constructor.
     * @param RpcOutputParameter $param
     */
    public function __construct($param)
    {
        parent::__construct(null);
        $this->param = $param;
    }


    public function writeVariable()
    {
        $format = <<<EOF
/**
     * @var %s %s
     */
    private $%s = %s;
     
EOF;
        $param = $this->param;

//        $format = "%sprivate $%s = %s;\n";
        if ($this->param->isRepeated()) {
            $value = "null";
        } elseif (is_null($this->param->getDefault())) {
            $value = "null";
        } elseif ($this->param->is_string()) {
            $value = '"' . strval($this->param->getDefault()) . '"';
        } else {
            $value = $this->param->getDefault();
        }
        return sprintf($format, $param->getVariableCommentString(),
            $param->getComment(),
            $param->getName(),
            $value);
    }

    public function writeAddFunction()
    {
        $param = $this->param;
        $format = <<<EOF
    /**
     * @var %s|null \$item
     */
    public function add%s(%s \$item = null)
    {
        if (is_null(\$this->%s)) {
            \$this->%s = [];
        }
        \$this->%s[] = \$item;
    }
EOF;
        return sprintf($format, $param->getOriginTypeDeclareAsString(),
            $param->getFunctionName(),
            $param->getOriginTypeDeclareAsString(),
            $param->getName(),
            $param->getName(),
            $param->getName());
    }
}