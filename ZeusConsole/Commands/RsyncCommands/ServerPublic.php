<?php
namespace ZeusConsole\Commands\RsyncCommands;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\ProcessBuilder;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/10/27
 * Time: 下午3:40
 */
class ServerPublic extends CommandBase
{

    /**
     * 服务器代码路径
     * @var string
     */
    private $codeSVNPath = 'svn://192.168.1.2/cooking_server/trunk/webgame';

    /**
     * svn Tag路径
     * @var string
     */
    private $codeSVNTagsPath = 'svn://192.168.1.2/cooking_server/tags/webGameTags/';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('rsync:serverpublic')
            ->setDescription('发布服务器代码到外网');
        $this->addOption('Tag', null, InputOption::VALUE_NONE, '是否自动从当前版本打Tag');
        $this->addOption('force', null, InputOption::VALUE_NONE, '不经过询问 强制发布');
        $this->addOption('publish', null, InputOption::VALUE_OPTIONAL, '发布到远程服务器,服务器版本号');
        $this->addOption('fullVersion',null,InputOption::VALUE_NONE,'是否是完整版发布.包括Test之类');

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
        //打Tag
        $fs = new Filesystem();
//        $publicVersion = $input->getArgument("publicVersion");


        if ($input->getOption("publish")) {
            $publicVersion = $input->getOption("publish");
        } else {
            //处理要打包的版本号
            $serverVersionPath = $this->codeSVNPath . "/include/constants/constants_serverVersion.php";
            $process = utils::createSvnProcess([
                'cat',
                $serverVersionPath,
            ]);

            $process->run();
            if ($process->getExitCode()) {
                $output->writeln("<error>" . $process->getErrorOutput() . "</error>");
                return 1;
            }

            //require 服务器版本号头文件
            $serverVersionContents = $process->getOutput();
            $serverVersionLocalPath = $this->getTagLocalPath() . "/constants_serverVersion.php";
            $fs->dumpFile($serverVersionLocalPath, $serverVersionContents);
            require $serverVersionLocalPath;
            $fs->remove($serverVersionLocalPath);
            //这里错误是正确的,因为要获取一个常量
            $publicVersion = \constants\constants_serverVersion::VERSION;
        }


        if (!$input->getOption('force')) {

            $helper = $this->getHelper('question');
            if ($helper instanceof QuestionHelper) {
                $question = new ConfirmationQuestion("是否发布服务器代码 版本号[$publicVersion]? (y/n):", false);
                $bundle = $helper->ask($input, $output, $question);
                if (!$bundle) {
                    $output->writeln('<error>放弃发布</error>');
                    return 1;
                }
            }
        }

        if ($input->getOption('Tag')) {
            $process = utils::createSvnProcess([
                'info',
                $this->getTagPath($publicVersion)
            ]);

            $process->run();

            if ($process->getExitCode() === 0) {
                $output->writeln('<error>tag 已经存在 :' . $publicVersion . '</error>');
                return 1;
            }

            $process = utils::createSvnProcess([
                'copy',
                $this->codeSVNPath,
                $this->getTagPath($publicVersion),
                '-m',
                $publicVersion . "released!",
            ]);

            $process->run();

            if ($this->verboseDebug) {
                var_dump($process->getCommandLine());
                var_dump($process->getOutput());
            }
            if (0 !== $process->getExitCode()) {
                $output->writeln('<error>tag 失败</error>');
                $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
                return 1;
            }

            $output->writeln('Tag 完成:' . $this->getTagPath($publicVersion));
        }


        //export服务器代码

        if ($input->getOption('publish')) {
            //不是打包模式,处理本地要上传服务器代码

            $fs->remove($this->getTagLocalPath());

            $process = utils::createSvnProcess([
                'export',
                $this->getTagPath($publicVersion),
                $this->getTagLocalPath(),
                '--force'
            ]);

            $process->run();
            if (0 !== $process->getExitCode()) {
                $output->writeln('<error>export 失败</error>');
                $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
                return 1;
            }
//            $output->write($process->getOutput());


            //只包含两个文件夹
            $finder = new Finder();
            $includeDirectories = [
                'htdocs',
                'include'
            ];
            $iterators = $finder
                ->depth(0)
                ->directories()
                ->exclude($includeDirectories)
                ->in($this->getTagLocalPath());


            //剔除文件夹
            foreach ($iterators as $file) {
                if ($file instanceof SplFileInfo) {
                    $fs->remove($file->getRealPath());
                }
            }

            $isFullVersion = $input->getOption("fullVersion");


            if(!$isFullVersion) {
                //只包含index.php文件
                $htdocsPath = $this->getTagLocalPath() . DIRECTORY_SEPARATOR . "htdocs" . DIRECTORY_SEPARATOR;
                $finder = new Finder();
                $iterators = $finder
                    ->depth('0')
                    ->notPath('/^index.php$/')
//                    ->notPath('/^gmtoolsindex.php$/')
                    ->in($htdocsPath);


                foreach ($iterators as $file) {
                    if ($file instanceof SplFileInfo) {
                        $fs->remove($file->getRealPath());
                    }
                }
            }

            $webHostAddress = "101.200.182.146";

            //Rsync同步
            $processBuilder = new ProcessBuilder();
            $processBuilder->setArguments([
                'rsync',
                '-vzrtp',
                '--progress',
                '--delete',
                $this->getTagLocalPath() . DIRECTORY_SEPARATOR,
                "101.200.182.146::rsync/servecode/www/"
            ]);

            $process = $processBuilder->getProcess();
            $process->setTimeout(null);

            $process->run();
            if (0 !== $process->getExitCode()) {
                $output->writeln('<error>Rsync 失败</error>');
                $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
                return 1;
            }
            $output->writeln($process->getOutput());

            $output->writeln([
                '<info>同步完成 :' . $publicVersion . '</info>',
                '运行如下命令发布:',
                "ssh root@" . $webHostAddress,
                'cd /svnRoot/webmastermachinetools/publicShells && sh publictoclient.sh'
            ]);
        }

        return 0;

    }

    private function getTagPath($version)
    {
        return $this->codeSVNTagsPath . 'tag_webgame_' . $version;
    }

    private function getTagLocalPath()
    {
        return utils::getTempDirectoryPath() . 'exportServerTags';
    }


}