<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/10/23
 * Time: 下午5:34
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters;


class LogicTemplatesReturnParameterWriter extends WriterBase
{


    public function writeFunctionResetToDefault()
    {
        $resetFormat = <<<EOF
    protected function initializeWithDefault()
    {
        parent::initializeWithDefault();
%resetFunctions%
        return \$this;
    }
EOF;

        $resetFunctions = "";
        $parameters = $this->generateClass->getReturnParameters();
        foreach ($parameters as $parameter) {
            if ($parameter->hasDefault()) {
                $resetFunctions .= $this->format_tab_2 . "\$this->reset{$parameter->getFunctionName()}ToDefault();\n";
            } elseif ($parameter->isRequire() && $parameter->isRepeated()) {
                //必须传递的数组
                $resetFunctions .= $this->format_tab_2 . "\$this->reset{$parameter->getFunctionName()}ToDefault();\n";
            }
        }

        $setData = ["%resetFunctions%" => $resetFunctions];
        return translator()->trans($resetFormat, $setData);
    }
}