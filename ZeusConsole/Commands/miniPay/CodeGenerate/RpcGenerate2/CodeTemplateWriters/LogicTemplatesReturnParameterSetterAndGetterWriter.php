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
     * @var %type% %comment%
     */
    //private \$%name% = %defaultValue%;
EOF;
        $param = $this->param;

        if ($this->param->isRepeated()) {
            $value = "null";
        } elseif (is_null($this->param->getDefault())) {
            $value = "null";
        } elseif ($this->param->is_string()) {
            $value = '"' . strval($this->param->getDefault()) . '"';
        } else {
            $value = $this->param->getDefault();
        }


        $setData = [
            "%comment%" => $param->getComment(),
            "%type%" => empty($param->getTypeDeclareAsString()) ? "mixed" : $param->getTypeDeclareAsString(),
            "%name%" => $param->getName(),
            "%defaultValue%" => $value
        ];

        return translator()->trans($format, $setData);
    }

    public function writeAddFunction()
    {
        $param = $this->param;
        $format = <<<EOF
        
    /**
     * @var %comment%|null \$item
     */
    public function add%FunctionName%(\$item = null)
    {
        \$origin_data = \$this->getOriginArrayData('%name%', []);
        \$origin_data[] = \$this->convertValue(\$item);
        \$this->setOriginArrayData('%name%', \$origin_data);
    }
EOF;
        $formatObject = <<<EOF
        
    /**
     * @var %comment%|null \$item
     */
    public function add%FunctionName%(\$item = null)
    {
        \$origin_data = \$this->getOriginArrayData('%name%', []);
        \$origin_data[] = \$this->convertValue(\$item);
        \$this->setOriginArrayData('%name%', \$origin_data);
    }
EOF;

        $setData = [
            "%comment%" => $param->getMessageClassName(),
            "%FunctionName%" => $param->getFunctionName(),
            "%type%" => $param->getMessageClassName(),
            "%name%" => $param->getName()
        ];

        if ($param->isObject()) {
            $setData = [
                "%comment%" => $param->getObjectFullClassName(),
                "%FunctionName%" => $param->getFunctionName(),
                "%type%" => $param->getObjectFullClassName(),
                "%name%" => $param->getName()
            ];
        }

        if ($param->isMessage() || $param->isObject()) {
            $message = translator()->trans($formatObject, $setData);
        } else {
            $message = translator()->trans($format, $setData);
        }
        return $message;


    }

    public function writeSetFunction()
    {
        $param = $this->param;
        $formatNormal = <<<EOF
        
    /**
     * @param %comment% \$%name%
     */
    public function set%FunctionName%(%type% \$%name% = null)
    {
        \$this->setOriginArrayData('%name%', \$%name%);
    }
EOF;

        $formatMessage = <<<EOF
        
    /**
     * @param %comment% \$%name%
     */
    public function set%FunctionName%($%name% = null)
    {
        \$this->setOriginArrayData('%name%', \$%name%);
    }
EOF;
        $formatMessageNotRepeat = <<<EOF
        
    /**
     * @param %comment% \$%name%
     */
    public function set%FunctionName%(\$%name% = null)
    {
        \$this->setOriginArrayData('%name%', \$%name%);
    }
EOF;

        $formatMessageRepeat = <<<EOF
        
    /**
     * @param %comment% \$%name%
     */
    public function set%FunctionName%(\$%name% = null)
    {
        \$setData = [];
        if (is_array(\$%name%)) {
            foreach (\$%name% as \$item) {
                \$setData[] = \$this->convertValue(\$item);
            }
        } else {
            \$setData = \$%name%;
        }
        \$this->setOriginArrayData('%name%', \$setData);
    }
EOF;
        $setData = [
            "%comment%" => $param->getVariableCommentString(),
            "%FunctionName%" => $param->getFunctionName(),
            "%type%" => $param->getTypeDeclareAsString(),
            "%name%" => $param->getName()
        ];

        if ($param->isMessage() && !$param->isRepeated()) {
            $message = translator()->trans($formatMessageNotRepeat, $setData);
        } else if ($param->isMessage() && $param->isRepeated()) {
            $message = translator()->trans($formatMessageRepeat, $setData);
        } else if ($param->isObject() && !$param->isRepeated()) {
            $message = translator()->trans($formatMessageNotRepeat, $setData);
        } else if ($param->isObject() && $param->isRepeated()) {
            $message = translator()->trans($formatMessageRepeat, $setData);

        } else {
            $message = translator()->trans($formatNormal, $setData);
        }

        return $message;
    }

    public function writeResetFunction()
    {
        $param = $this->param;
        $formatNormal = <<<EOF
        
    /**
     * @param %comment% \$%name%
     */
    public function reset%FunctionName%ToDefault(\$%name% = %defaultValue%)
    {
        \$this->setOriginArrayData('%name%', \$%name%);
    }
EOF;

        if ($this->param->isRepeated()) {
            $defaultValue = "[]";
        } elseif (is_null($this->param->getDefault())) {
            $defaultValue = "null";
        } elseif ($this->param->is_string()) {
            $defaultValue = '"' . strval($this->param->getDefault()) . '"';
        } else {
            $defaultValue = $this->param->getDefault();
        }


        $setData = [
            "%comment%" => $param->getVariableCommentString(),
            "%FunctionName%" => $param->getFunctionName(),
            "%type%" => $param->getTypeDeclareAsString(),
            "%name%" => $param->getName(),
            "%defaultValue%" => $defaultValue
        ];

        $message = translator()->trans($formatNormal, $setData);
        return $message;
    }
}