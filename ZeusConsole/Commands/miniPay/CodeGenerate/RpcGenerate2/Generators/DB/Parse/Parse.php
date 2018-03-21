<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 20/03/2018
 * Time: 4:58 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\Parse;

/**
 * Class Parse
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\Parse
 */
abstract class Parse
{
//    protected $parseData = null;

    /**
     * @param $parseData
     * @return static
     */
    static public function parse($parseData)
    {
        $ins = new static();
//        $ins->parseData = $parseData;
        $ins->parseImpl($parseData);
        return $ins;
    }

    abstract protected function parseImpl($parseData);

    /**
     * @return array
     */
    public function toArray()
    {
        return [];
    }
}