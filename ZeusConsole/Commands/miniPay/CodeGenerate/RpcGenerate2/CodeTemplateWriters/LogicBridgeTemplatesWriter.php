<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 27/12/2017
 * Time: 10:07 AM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters;


class LogicBridgeTemplatesWriter extends WriterBase
{
    /**
     * @return array
     */
    function getParameterVarStrings()
    {
        $vars = [];
        $parameters = $this->generateClass->getParameters();
        foreach ($parameters as $parameter) {
            $vars[] = $this->format_tab_3.$parameter->getParameterVarString();
        }
        return $vars;
    }
}