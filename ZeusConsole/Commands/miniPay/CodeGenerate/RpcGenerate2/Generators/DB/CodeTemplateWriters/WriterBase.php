<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 21/03/2018
 * Time: 11:03 AM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\CodeTemplateWriters;

use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\DBGeneratorClass;

/**
 * Class WriterBase
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\CodeTemplateWriters
 */
abstract class WriterBase
{
    protected $format_tab = "    ";
    protected $format_tab_2 = "        ";
    protected $format_tab_3 = "            ";

    /**
     * @var $generateClass DBGeneratorClass
     */
    protected $generateClass;

    /**
     * WriterBase constructor.
     * @param DBGeneratorClass $generateClass
     */
    public function __construct(DBGeneratorClass $generateClass)
    {
        $this->generateClass = $generateClass;
    }
}