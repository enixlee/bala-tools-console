<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/9/13
 * Time: 上午8:46
 */

namespace ZeusConsole;


use ZeusConsole\Application\ApplicationBase;
use ZeusConsole\Commands\GreetCommand;
use ZeusConsole\Commands\miniPay\CodeGenerate\JSCodeGenerate\RpcJsUrlsExport;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\RpcGenerate2;
use ZeusConsole\Commands\miniPay\CodeGenerate\VueCodeGenerate\VueRpcExport;
use ZeusConsole\Commands\miniPay\Utils\encodePassword;
use ZeusConsole\Commands\miniPay\Utils\verifyEncodePassword;
use ZeusConsole\Commands\System\dumpConfig;
use ZeusConsole\Commands\System\showConfig;

class ConsoleAppBala extends ApplicationBase
{


    protected function getDefaultCommands()
    {
        $Commands = parent::getDefaultCommands();
        $Commands[] = new GreetCommand ();


        $Commands[] = new showConfig();
        $Commands[] = new dumpConfig();


        $Commands[] = new RpcGenerate2();

        $Commands[] = new RpcJsUrlsExport();

        $Commands[] = new VueRpcExport();
        $Commands[] = new encodePassword();
        $Commands[] = new verifyEncodePassword();


        return $Commands;
    }
}