<?php

namespace ZeusConsole\Application;
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/9/13
 * Time: 上午8:49
 */
class ApplicationBase extends \Symfony\Component\Console\Application
{
    /**
     * Constructor.
     *
     *
     * @api
     */
    public function __construct()
    {
        parent::__construct('客户端控制台', getConfig('version', 0) .
            " buildTime:" . getConfig('buildTime', date('Ymd-His')));
    }
}