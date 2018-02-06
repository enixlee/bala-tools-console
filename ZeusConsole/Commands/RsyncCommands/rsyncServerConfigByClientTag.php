<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/3/31
 * Time: 下午3:55
 */

namespace ZeusConsole\Commands\RsyncCommands;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use ZeusConsole\Commands\Client\ClientPackData;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

class rsyncServerConfigByClientTag extends CommandBase
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("rsync:ResourceByClientTag")
            ->setDescription("
            通过客户端Tag版本,同步开发服务器配置 
            这就是一个助手方法,有很多参数是写死的,就这样吧, 
            例如:客户端Tag的SVN路径固定为:
            svn://192.168.1.2/cooking_packages/trunk/clientUpgradePackages");

        $this->addArgument("luaVersion", InputArgument::REQUIRED, "增量包的lua版本号");

        $this->addOption("serverRsync", null, InputOption::VALUE_OPTIONAL,
            "需要同步的服务器的rsync地址", "192.168.1.3::apiphone_cookinggame");
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
        $luaVersion = $input->getArgument("luaVersion");
        $serverRsync = $input->getOption("serverRsync");

        $clientTagSvnPath = "svn://192.168.1.2/cooking_packages/trunk/clientUpgradePackageInfo/packagelist.json";
//        $clientTagSvnPath .= DIRECTORY_SEPARATOR . "$luaVersion";

        $process = utils::createSvnProcess(
            [
                'cat',
                $clientTagSvnPath
            ]
        );
        $process->run();


        //打包文件不存在
        if ($process->getExitCode()) {
            $output->writeln("<error>" . $process->getErrorOutput() . "</error>");
            return 1;
        }
        $packages = utils::getSerializer()->deserialize($process->getOutput(),
            'ZeusConsole\Commands\Client\ClientPackData[]', 'json');
        if ($this->isVerboseDebug()) {
            var_dump($packages);
        }

        //目标Tag的打包信息
        $findPackage = null;
        foreach ($packages as $package) {
            /**
             * @var $package ClientPackData
             */
            if ($package->getClientversion() === $luaVersion) {
                $findPackage = $package;
                break;
            }
        }

        if (is_null($findPackage)) {
            $output->writeln("<error>没有找到对应的增量包Tag:" . $luaVersion . "</error>");
            return 2;
        }

        if ($this->isVerboseDebug()) {
            var_dump(["增量包信息:" => $findPackage]);
        }

        //需要导出的指定版本号的资源
        $csvTag = -1;
        if (!is_null($findPackage->getCsvSvnRevision())) {
            $csvTag = $findPackage->getCsvSvnRevision();
        }


        //开始同步
//        $process
        $command = $this->getApplication()->find('rsync:gameconfig');
        $commandParams = [
            'command' => 'rsync:gameconfig',
            '--rsync-serveraddr' => $serverRsync . "/configdata/",
            '--csvRevision' => $csvTag,
        ];
        $returnCode = $command->run(new ArrayInput($commandParams),
            $output);

        if ($returnCode != 0) {
            return $returnCode;
        }


        $command = $this->getApplication()->find('rsync:gameMapConfig');
        $commandParams = [
            'command' => 'rsync:gameMapConfig',
            '--rsync-serveraddr' => $serverRsync . "/mapdata/",
        ];
        $returnCode = $command->run(new ArrayInput($commandParams),
            $output);

        if ($returnCode != 0) {
            return $returnCode;
        }
        $output->writeln("<info>发布完成,去愉快的玩耍吧</info>");

        return null;
    }


}