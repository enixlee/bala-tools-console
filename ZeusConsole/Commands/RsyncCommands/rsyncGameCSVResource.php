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
class rsyncGameCSVResource extends CommandBase
{
    private function getExportTempPath()
    {
        return utils::getTempDirectoryPath() . 'RsyncTempPath';
    }

    protected function configure()
    {
        $this->setName('rsync:gameconfig')
            ->setDescription('同步CSV配置到内网开发服务器192.168.1.3');
        $definition = new InputDefinition ();
        $definition->addArguments([
            new InputArgument ('localpath', InputArgument::OPTIONAL, '本地的config路径 或者远程SVN的路径'
                , 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格'),

        ]);
        $definition->addOptions([
            new InputOption ('rsync-username', 'rs-user', InputOption::VALUE_REQUIRED, 'rsync服务器用户名', 'backup'),
            new InputOption ('rsync-password', 'rs-passwd', InputOption::VALUE_REQUIRED, 'rsync服务器密码', 'backup'),
            new InputOption ('rsync-serveraddr', 'rs-addr', InputOption::VALUE_REQUIRED, "rsync服务器目录地址",
                '192.168.1.3::api_cookinggame/configdata/')
        ]);
        $this->setDefinition($definition);

        $this->addOption('csvRevision', null, InputOption::VALUE_OPTIONAL, "如果是SVN路径,从特殊的版本号打包,0为最新", 0);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rsync_username = $input->getOption('rsync-username');
        $rsync_addr = $input->getOption('rsync-serveraddr');
        $command = $rsync_username . '@' . $rsync_addr;

        $localPath = $input->getArgument('localpath');
        $fs = new Filesystem ();

        if (util::starts_with($localPath, 'svn://')) {
            $fs->remove($this->getExportTempPath());
            $fs->mkdir($this->getExportTempPath());

            $phpPath = $this->getExportTempPath();
            $exportCSVCommand = $this->getApplication()->find('csv:export');
            $exportRevision = $input->getOption("csvRevision");
            $exportCSVArgs = [
                'command' => 'csv:export',
                'csv-path' => $localPath,
                'export-path' => $phpPath,
                '--export-format' => 'php',
                '-s' => true,
                '--csvRevision' => $exportRevision
            ];
            $exportCSVInput = new ArrayInput($exportCSVArgs);
            $returnCode = $exportCSVCommand->run($exportCSVInput, $output);

            if ($returnCode != 0) {
                $output->writeln('<error>导出配置文件失败!</error>');
                return 0;
            }
            $localPath = $this->getExportTempPath();

        }

        $localPath = $localPath . DIRECTORY_SEPARATOR;

        if (!$fs->exists($localPath)) {

            $output->writeln('<error>本地配置文件 localpath: ' . $localPath . ' 路径不存在</error>');
            return 1;
        }

        $builder = new ProcessBuilder ();
        $builder->setEnv('RSYNC_PASSWORD', 'backup');
        $builder->setPrefix([
            'rsync'
        ]);
        $builder->setArguments([
            '--progress',
            '-vrt',
            '--delete',
            $localPath,
            $command
        ]);
        $process = $builder->getProcess();
//        $output->writeln($process->getCommandLine());
        $process->run(function ($type, $buffer) use ($output) {
//            $output->writeln($buffer);
        });

        $output->writeln($process->getOutput());

        return 0;
    }
}