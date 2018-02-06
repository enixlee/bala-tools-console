<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 2015/9/25
 * Time: 15:30
 */
namespace ZeusConsole\Commands\MakeClass;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

/**
 *生成一个按照MakeDbsPlayerCell.rtf规则创建并继承于dbs_baseplayer的类
 * Class MakeDbsPlayerCell
 * @package ZeusConsole\Commands\MakeClass
 */
class MakeDbsPlayerCell extends CommandBase
{
    protected function configure()
    {
        $this->setName('makeClass:playercell')
            ->setDescription("创建Playercell");

        $this->addArgument('className', InputArgument::REQUIRED, 'Service类名');
//        $this->addArgument('dest',InputArgument::OPTIONAL,'输入文件位置',$_SERVER['PWD']);
        //判断当前的运行环境
        $this->addArgument('dest', InputArgument::OPTIONAL, '输入文件位置', utils::judgePath());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument("className");

        $fs = new Filesystem();
        $templateFilePath = __DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates" . DIRECTORY_SEPARATOR . 'MakeDbsPlayerCell.rtf';
        $templateString = file_get_contents($templateFilePath);
        $outString = translator()->trans($templateString,
            [
                '{{ className }}' => $className,
            ]);
        $outFilePath = $input->getArgument('dest') . DIRECTORY_SEPARATOR . 'dbs_' . $className . '.php';
        $fs->dumpFile($outFilePath, $outString);

        $output->writeln('<info>创建成功' . $outFilePath . '</info>');

    }

}