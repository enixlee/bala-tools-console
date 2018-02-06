<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/10/13
 * Time: 上午11:32
 */

namespace ZeusConsole\Commands\Client;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;
use utilphp\util;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

class ClientBuildIOS extends CommandBase
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('client:buildIOS');
        $this->setDescription('编译客户端完整包');
        $this->addArgument('clientPath', InputArgument::REQUIRED, "客户端所在目录,最后是clientcocos,例如:\n
        /Users/zhipeng/Documents/Object/cooking_client/code/clientcocos");
        $this->addOption('scheme', null, InputOption::VALUE_OPTIONAL, 'IOS编译Scheme 默认为 YYGameInhouse', 'YYGameInhouse');

        $this->addOption('testMode', null, InputOption::VALUE_NONE, '是否为测试模式,如果是测试模式,不检测是否存在安装包,不上传SVN');
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $fs->remove($this->getBuildDirectory());
        $fs->mkdir($this->getBuildDirectory());
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

        $ProjectPath = $input->getArgument('clientPath');
        $fs = new Filesystem();
        if (!$fs->exists($ProjectPath)) {
            $output->writeln('<error>.客户端路径不存在:' . $ProjectPath . '</error>');
            return 1;
        }

        $isoProjectPath = $ProjectPath . DIRECTORY_SEPARATOR . 'projects/YYGame/proj.ios/';
        if (!$fs->exists($isoProjectPath)) {
            $output->writeln('<error>.IOS工程路径不存在:' . $isoProjectPath . '</error>');
            return 2;
        }
//
//
        $testMode = $input->getOption('testMode');
        if ($testMode) {
            $output->writeln("<comment>开启测试模式</comment>");
        }


        if (!$testMode) {
            //打一个C++变更的空增量包,主要是增加发布记录
            $ClientPack = $this->getApplication()->find('client:pack');
            $ClientPackParams = [
                'command' => 'csv:pack',
                '--ignoreCheckCppVersion' => true
            ];
            $returnCode = $ClientPack->run(new ArrayInput($ClientPackParams), $output);
            if ($returnCode != 0) {
                return $returnCode;
            }
        }


        $output->write('同步到最新客户端代码...');
        $process = utils::createSvnProcess(['up']);
        $process->run();

        $output->writeln('<info>同步代码完成!!</info>');

        $output->write('开始导出客户端数据表...');
        $jsonPath = $ProjectPath . DIRECTORY_SEPARATOR . 'projects/YYGame/Resources/YY/Game/json';
        $exportCSVCommand = $this->getApplication()->find('csv:export');
        $exportCSVArgs = [
            'command' => 'csv:export',
            'csv-path' => 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格',
            'export-path' => $jsonPath,
            '--export-format' => 'lua',
            '-c' => true,

        ];
        $exportCSVInput = new ArrayInput($exportCSVArgs);
        $returnCode = $exportCSVCommand->run($exportCSVInput, $output);

        if ($returnCode != 0) {
            $output->writeln('<error>导出配置文件失败!</error>');
            return 3;
        } else {
            $output->writeln('<info>导出配置文件完成</info>');
        }
        $IOSScheme = $input->getOption('scheme');

        $output->write('编译IOS客户端...');

        $processBuilder = new ProcessBuilder();
        $processBuilder->setWorkingDirectory($isoProjectPath);
        $processBuilder->setPrefix('xcodebuild');
        $processBuilder->setArguments(
            [
                '-scheme',
                $IOSScheme,
                'clean',
                '-alltargets',
                'archive',
                '-configuration',
                'Release.inhouse',
                '-archivePath',
                $this->getBuildDirectory() . "YYGame.xcarchive"
            ]);
        $process = $processBuilder->getProcess();
        $process->setTimeout(null);
        $returnCode = $process->run();
        if ($output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG) {
            var_dump($process->getOutput());
            var_dump($process->getErrorOutput());
        }
        if ($returnCode) {
            var_dump($process->getErrorOutput());

            $output->writeln("<error>编译失败</error>");
            return 5;
        }

        $output->writeln('编译IOS客户端完成');
        $output->write('开始打包ipa...');
        $ipaFileName = $this->getBuildDirectory() . 'YYGame_' . date('Ymd-His') . '.ipa';
        $processBuilder = new ProcessBuilder();
        $processBuilder->setWorkingDirectory($isoProjectPath);
        $processBuilder->setPrefix('xcodebuild');
        $processBuilder->setArguments(
            [
                '-exportArchive',
                '-exportFormat',
                'ipa',
                '-archivePath',
                $this->getBuildDirectory() . "YYGame.xcarchive",
                '-exportPath',
                $ipaFileName,
                '-exportProvisioningProfile',
                'ctgame_inhouse'
            ]);
        $process = $processBuilder->getProcess();
        $process->setTimeout(null);
        $returnCode = $process->run();
        if ($output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG) {
            var_dump($process->getOutput());
            var_dump($process->getErrorOutput());
        }

        if ($returnCode) {
            var_dump($process->getErrorOutput());
            $output->writeln("<error>开始打包ipa失败</error>");
            return 5;
        }

        
        $output->writeln([
            'Ipa路径:',
            $ipaFileName,

        ]);

        $luaVersion = $this->getLuaCodeVersion("svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YY/appversion.lua");

        $cppVersion = $this->getCppVersion("svn://192.168.1.2/project/code/clientcocos/scripting/lua/cocos2dx_support/LuaYYEngine/YYConstPlatform.h");

        $ipaSVNFileName = "YYGame_lua($luaVersion)_cpp($cppVersion).ipa";


        if (!$testMode) {
            $output->writeln([
                '开始备份到SVN'
            ]);

            $ipaSVNPath = "svn://192.168.1.2/cooking_packages/trunk/installPackages/" . $cppVersion . "/" . $ipaSVNFileName;

            $process = utils::createSvnProcess([
                'import',
                $ipaFileName,
                $ipaSVNPath,
                '-m',
                'commit ipa Package, FileName:' . $ipaSVNFileName
            ]);
            $process->run();

            $output->writeln([
                '<info>备份完成</info>',
                $ipaSVNPath
            ]);
        }

        $output->writeln("<info>打包完成</info>");


    }

    private function getBuildDirectory()
    {
        return utils::getTempDirectoryPath() . 'app_build' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取将要打包的版本号
     * @param $svnPath
     * @return string
     */
    private function getLuaCodeVersion($svnPath)
    {
        $process = utils::createSvnProcess([
            'cat',
            $svnPath,
        ]);
        $process->run();


        $lines = explode("\n", $process->getOutput());
        foreach ($lines as $line) {
            if (util::starts_with($line, 'APP_VERSION = ')) {
                $Version = explode(" ", $line)[2];
                $Version = trim($Version);
                $Version = trim($Version, "\"");
//                $Version = strtr($Version, ["." => '_']);
//                var_dump($Version);
                return $Version;
            }
        }
        return "";

    }

    /**
     * 获取Cpp版本号
     * @param $svnPath
     * @return string
     */
    private function getCppVersion($svnPath)
    {

        $process = utils::createSvnProcess([
            'cat',
            $svnPath
        ]);
        $process->run();


        $lines = explode("\n", $process->getOutput());
        foreach ($lines as $line) {
            if (util::starts_with($line, 'static const char * CC_CPP_VERSION = ')) {
                $Version = explode("=", $line)[1];
                $Version = trim($Version);
                $Version = trim($Version, ';');
                $Version = trim($Version);
                $Version = trim($Version, "\"");
                return $Version;
            }
        }
        return "";

    }


}