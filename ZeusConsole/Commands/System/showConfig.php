<?php
namespace ZeusConsole\Commands\System;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZeusConsole\Commands\CommandBase;

/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/12/9
 * Time: 下午8:34
 */
class showConfig extends CommandBase
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('System:showConfig')->setDescription("显示系统配置");
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
        $configs = getConfig();
        foreach ($configs as $key => $value) {
            $output->writeln("<info>$key:$value</info>");
        }
    }

}