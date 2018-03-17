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


    /**
     * @return string
     */
    function writeUseDocument()
    {
        $format = <<<EOF
use Pluto\Foundation\RPC\Caller\RpcCallerParameter;
use Pluto\Foundation\RPC\RPCCommandResult;
EOF;
        if ($this->generateClass->getRpcTypeConfig('isXicService', false)) {
            $format .= "\nuse Pluto\Foundation\XicService\XicCaller;\n";
        }

        return $format;
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

    function writeWithParameters()
    {
        $format = <<<EOF
        \$parameter->setOption(XicCaller::OPTION_SERVER_NAME, "%serviceName%");
        \$parameter->setCaller(app(XicCaller::class));

EOF;

        $content = "";
        if ($this->generateClass->getRpcTypeConfig('isXicService', false)) {
            $name = $this->generateClass->getRpcTypeConfig('name');
            $serviceName = $this->generateClass->getRpcTypeConfig('serviceName', $name);
            $content = translator()->trans($format,
                [
                    "%serviceName%" => $serviceName
                ]);


        }
        return $content;
    }
}