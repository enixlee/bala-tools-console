<?php

namespace ZeusConsole\Commands\RsyncCommands;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;
use utilphp\util;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

/**
 * 同步CSV配置到内网开发服务器192.168.1.3
 * Class RSyncGameConfig
 * @package ZeusConsole\Commands
 */
class rsyncGameMapResource extends CommandBase
{
    private function getExportTempPath()
    {
        return utils::getTempDirectoryPath() . 'RsyncTempPath';
    }

    protected function configure()
    {
        $this->setName('rsync:gameMapConfig')
            ->setDescription('同步地图数据到内网开发服务器192.168.1.3');
        $definition = new InputDefinition ();
        $definition->addArguments([
            new InputArgument ('svnMapPath', InputArgument::OPTIONAL, 'SVN中的地图导出路径'
                , 'svn://192.168.1.2/project/code/测试资源/servermap'),

        ]);
        $definition->addOptions([
            new InputOption ('rsync-username', 'rs-user', InputOption::VALUE_REQUIRED, 'rsync服务器用户名', 'backup'),
            new InputOption ('rsync-password', 'rs-passwd', InputOption::VALUE_REQUIRED, 'rsync服务器密码', 'backup'),
            new InputOption ('rsync-serveraddr', 'rs-addr', InputOption::VALUE_REQUIRED, "rsync服务器目录地址",
                '192.168.1.3::api_cookinggame/mapdata/')
        ]);
        $this->setDefinition($definition);

        $this->addOption('mapRevision', null, InputOption::VALUE_OPTIONAL, "如果是SVN路径,从特殊的版本号打包,0为最新", 0);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rsync_username = $input->getOption('rsync-username');
        $rsync_addr = $input->getOption('rsync-serveraddr');
        $command = $rsync_username . '@' . $rsync_addr;

        $svnMapPath = $input->getArgument('svnMapPath');
        $fs = new Filesystem ();

        $fs->remove($this->getExportTempPath());
        $fs->mkdir($this->getExportTempPath());

        $exportRevision = $input->getOption("mapRevision");
        if (intval($exportRevision) === 0) {
            $exportRevision = utils::getSvnRevision($svnMapPath);
        }

        $process = utils::createSvnProcess([
            'export',
            '--force',
            '-r',
            $exportRevision,
            $svnMapPath,
            $this->getExportTempPath(),
        ]);
        $process->run();

        if ($this->isVerboseDebug()) {
            $output->writeln($process->getOutput());
        }
        if ($process->getExitCode()) {
            $output->writeln("<error>" . $process->getErrorOutput() . "</error>");
            return 1;
        }

        $localMapPath = $this->getExportTempPath() . DIRECTORY_SEPARATOR;
        $builder = new ProcessBuilder ();
        $builder->setEnv('RSYNC_PASSWORD', 'backup');
        $builder->setPrefix([
            'rsync'
        ]);
        $builder->setArguments([
            '--progress',
            '-vrt',
            '--delete',
            $localMapPath,
            $command
        ]);
        $process = $builder->getProcess();
        if ($this->isVerboseDebug()) {
            $output->writeln($process->getCommandLine());
        }
        $process->run(function ($type, $buffer) use ($output) {
//            $output->writeln($buffer);
        });

        if ($process->getExitCode()) {
            $output->writeln("<error>" . $process->getErrorOutput() . "</error>");
            return 2;
        }

        $output->writeln(
            [
                "<info>同步完成!!</info>",
                $process->getOutput()
            ]);

        return 0;
    }
}