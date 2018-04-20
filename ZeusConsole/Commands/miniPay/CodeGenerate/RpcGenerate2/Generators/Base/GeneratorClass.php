<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 5:53 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\Base;


use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

interface GeneratorClass
{

    /**
     * 生成代码
     * @param SplFileInfo $file
     * @param OutputInterface $output
     * @return int 0失败,1成功,2忽略
     */
    public function generateCode(SplFileInfo $file, OutputInterface $output);

    /**
     * 失败
     */
    const ReturnFailed = 0;
    /**
     * 成功
     */
    const ReturnSuccess = 1;
    /**
     * 忽略
     */
    const ReturnIgnore = 2;


    /**
     *
     */
    const YAML_TYPE_RPC = 'rpc';

    /**
     *
     */
    const YAML_TYPE_OBJECT = 'object';
}