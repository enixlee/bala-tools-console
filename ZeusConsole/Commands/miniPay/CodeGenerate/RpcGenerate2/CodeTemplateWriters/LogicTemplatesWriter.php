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
}