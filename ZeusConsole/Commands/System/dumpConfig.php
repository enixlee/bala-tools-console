<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/12/9
 * Time: 下午8:40
 */

namespace ZeusConsole\Commands\System;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use ZeusConsole\Commands\CommandBase;

/**
 * Class dumpConfig
 * @package ZeusConsole\Commands\System
 */
class dumpConfig extends CommandBase
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('System:dumpConfig')->setDescription("dump 出用户配置");
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allConfigs = getConfig();
        unset($allConfigs['version']);
        unset($allConfigs['buildTime']);
        $configString = Yaml::dump($allConfigs);

        var_dump($configString);

        $fs = new Filesystem();
        $fs->dumpFile(ZeusConfigPath(), $configString);

        return 0;
    }


}