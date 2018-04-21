<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 8:00 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\CodeTemplateWriters;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\Parameter\ObjectParameter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\YamlObjectGeneratorClass;

class ObjectWriter extends WriterBase
{
    /**
     * @var YamlObjectGeneratorClass
     */
    protected $mainClass;

    /**
     * ObjectWriter constructor.
     * @param YamlObjectGeneratorClass $mainClass
     */
    public function __construct($mainClass)
    {
        $this->mainClass = $mainClass;
    }


    public function writeClassName()
    {
        $format = <<<EOF
class %className%\n
EOF;
        if (!is_null($this->mainClass->getExtends())) {
            $format = <<<EOF
class %className% extends %parent%\n
EOF;
        }


        $setData = [
            "%className%" => $this->mainClass->getClassName(),
            "%parent%" => $this->mainClass->getExtends(),
        ];

        return translator()->trans($format, $setData);
    }


}