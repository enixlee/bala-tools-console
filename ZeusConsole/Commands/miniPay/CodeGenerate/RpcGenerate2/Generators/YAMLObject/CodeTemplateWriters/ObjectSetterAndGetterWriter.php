<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 8:00 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\CodeTemplateWriters;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\Parameter\ObjectParameter;

class ObjectSetterAndGetterWriter extends WriterBase
{
    /**
     * @var ObjectParameter
     */
    private $param;

    /**
     * ObjectSetterAndGetterWriter constructor.
     * @param ObjectParameter $param
     */
    public function __construct($param)
    {
        $this->param = $param;
    }


    public function writeVariable()
    {
        $format = <<<EOF

    /**
     * @var %type% %comment%
     */
    private \$%name% = %defaultValue%;
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


        $setData = [
            "%comment%" => $param->getComment(),
            "%type%" => $param->getVariableCommentString(),
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
    public function add%FunctionName%(%type% \$item = null)
    {
        if (is_null(\$this->%name%)) {
            \$this->%name% = [];
        }
        \$this->%name%[] = \$item;
    }
EOF;

        $setData = [
            "%comment%" => $param->getTypeDeclareAsString(),
            "%FunctionName%" => $param->getFunctionName(),
            "%type%" => $param->getTypeDeclareAsString(),
            "%name%" => $param->getName()
        ];
        $message = translator()->trans($format, $setData);
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
        \$this->%name% = \$%name%;
    }
EOF;

        $formatMessage = <<<EOF
        
    /**
     * @param %comment% \$%name%
     */
    public function set%FunctionName%($%name% = null)
    {
        \$this->%name% = \$%name%;
    }
EOF;
        $formatMessageNotRepeat = <<<EOF
        
    /**
     * @param %comment% \$%name%
     */
    public function set%FunctionName%(\$%name% = null)
    {
        if (is_array(\$%name%)) {
            \$%name% = %type%::formArray($%name%);
        }
        \$this->%name% = \$%name%;
    }
EOF;
        $setData = [
            "%comment%" => $param->getVariableCommentString(),
            "%FunctionName%" => $param->getFunctionName(),
            "%type%" => $param->getTypeDeclareAsString(),
            "%name%" => $param->getName()
        ];

        if ($param->isObject() && !$param->isRepeated()) {
            $message = translator()->trans($formatMessageNotRepeat, $setData);
        } else if ($param->isObject() && $param->isRepeated()) {
            $message = translator()->trans($formatMessage, $setData);
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
        \$this->%name% = \$%name%;
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