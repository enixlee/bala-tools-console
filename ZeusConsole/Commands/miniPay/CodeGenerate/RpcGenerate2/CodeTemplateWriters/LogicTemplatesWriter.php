<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/10/23
 * Time: 下午5:34
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters;


class LogicTemplatesWriter extends WriterBase
{
    function getStaticParameters()
    {
        $parameters = $this->generateClass->getParameters();
        $returnParams = [];
        foreach ($parameters as $parameter) {
            $p = $this->format_tab . $this->format_tab . $parameter->getParameterAsArrayKey() . "," . $parameter->getParameterAsArrayKeyDescription();
            $returnParams [] = $p;
        }
        return $returnParams;
    }

    function writeOptions()
    {
        $options = $this->generateClass->getOptions();
        $format = "\"%key%\" => \"%value%\",";

        $values = "";
        foreach ($options as $key => $value) {

            $values .= translator()->trans($format,
                [
                    "%key%" => $key,
                    "%value%" => strval($value)
                ]);
        }


        $format = <<<EOF
    /**
     * @var array
     */
    public static \$%name%Options = [
        %values%
    ];
EOF;

        $content = translator()->trans($format,
            [
                "%name%" => $this->generateClass->getClassName(),
                "%values%" => $values
            ]);

        return $content;

    }
}