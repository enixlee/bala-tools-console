<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/19
 * Time: 下午3:46
 */

namespace ZeusConsole\Commands\CSV;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZeusConsole\Commands\CommandBase;

/**
 * Class ExportCsvServerFromSVN
 * @package ZeusConsole\Commands\CSV
 */
class ExportCsvServerFromSVN extends CommandBase
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('csv:export-help-svn');
        $this->setDescription('从svn导出数据表格的简易方法');
        $this->addArgument('export-path', InputArgument::REQUIRED, 'csv数据导出到的位置');
        $this->addArgument('svn-path', InputArgument::OPTIONAL, 'svn路径', 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格');

        $this->addOption('export-client', 'c', InputOption::VALUE_NONE, '导出客户端');
        $this->addOption('export-server', 's', InputOption::VALUE_NONE, '导出服务器数据');

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
//        $exportPath = $input->getArgument('export-path');
//        $svnpath
    }


}