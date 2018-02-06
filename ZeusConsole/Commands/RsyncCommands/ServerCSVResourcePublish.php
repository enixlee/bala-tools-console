<?php
namespace ZeusConsole\Commands\RsyncCommands;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
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
 * 发布CSV资源文件
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/10/27
 * Time: 下午3:40
 */
class ServerCSVResourcePublish extends CommandBase
{

    /**
     * 数据表路径
     * @var string
     */
    private $csvSVNPath = 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格';


    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('rsync:serverCSVResourcePublish')
            ->setDescription('发布CSV资源文件代码到外网');
        $this->addArgument('publishVersion', InputArgument::OPTIONAL,
            '存放CSV资源文件夹SVN版本号,例如 23000.0为最新版本', 0);
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

        $publishVersion = $input->getArgument("publishVersion");

        var_dump($publishVersion);

        $fs = new Filesystem ();
        $fs->remove($this->getExportTempPath());
        $fs->mkdir($this->getExportTempPath());

        $exportCSVCommand = $this->getApplication()->find('csv:export');
        $exportCSVArgs = [
            'command' => 'csv:export',
            'csv-path' => $this->csvSVNPath,
            'export-path' => $this->getExportTempPath(),
            '--export-format' => 'php',
            '-s' => true,
            '--csvRevision' => $publishVersion
        ];
        $exportCSVInput = new ArrayInput($exportCSVArgs);
        $returnCode = $exportCSVCommand->run($exportCSVInput, $output);

        if ($returnCode != 0) {
            $output->writeln('<error>导出配置文件失败!</error>');
            return 0;
        }

        //export服务器代码
        $webHostAddress = "101.200.182.146";

        //Rsync同步
        $processBuilder = new ProcessBuilder();
        $processBuilder->setArguments([
            'rsync',
            '-vzrtp',
            '--progress',
            '--delete',
            $this->getExportTempPath() . DIRECTORY_SEPARATOR,
            "101.200.182.146::rsync/configdata/"
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
            '<info>同步完成 :' . $publishVersion . '</info>',
            '运行如下命令发布:',
            "ssh root@" . $webHostAddress,
            'cd /svnRoot/webmastermachinetools/publicShells && sh publishGameConfigToclient.sh'
        ]);

        return 0;

    }


    private function getExportTempPath()
    {
        return utils::getTempDirectoryPath() . 'RsyncTempPath';
    }


}