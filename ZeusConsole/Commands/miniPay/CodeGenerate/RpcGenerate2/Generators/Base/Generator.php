<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 5:51 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\Base;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Generator
{
    /**
     * 生成代码
     * @param OutputInterface $output
     * @return int 0失败,1成功,2忽略
     */
    public function generate(InputInterface $input, OutputInterface $output);
}