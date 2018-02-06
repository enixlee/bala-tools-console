<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/10/23
 * Time: 下午5:34
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\RpcGenerateClass2;

abstract class WriterBase
{
    protected $format_tab = "    ";
    protected $format_tab_2 = "        ";
    protected $format_tab_3 = "            ";
    /**
     * @var RpcGenerateClass2
     */
    protected $generateClass;

    /**
     * LogicTestTemplatesWriter constructor.
     * @param $generateClass
     */
    public function __construct($generateClass)
    {
        $this->generateClass = $generateClass;
    }
}