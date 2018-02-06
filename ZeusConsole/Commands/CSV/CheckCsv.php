<?php

namespace ZeusConsole\Commands\CSV;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;
use utilphp\util;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

/**
 * Class CheckCsv
 * @package ZeusConsole\Commands\CSV
 */
class CheckCsv extends CommandBase
{
    protected function configure()
    {
        $this->setName('csv:check')->setDescription('检查csv格式');
        $this->addArgument('csv_path', InputArgument::OPTIONAL, 'CSV数据源路径,支持SVN路径'
            , 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格');


    }

    private function getSvnCachePath()
    {
        return utils::getTempDirectoryPath() . 'CSVExportFromSvn';
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
        $csv_path = $input->getArgument('csv_path');
        $fs = new Filesystem();
        if (util::starts_with($csv_path, 'svn://')) {

            $fs->remove($this->getSvnCachePath());

            $output->writeln('开始从SVN导出数据表到本地 ...');
            $process = utils::createSvnProcess([
                'export',
                $csv_path,
                $this->getSvnCachePath(),
                '--force'
            ]);
            $process->run();
            if ($output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
                $output->writeln($process->getOutput());
                $output->writeln($process->getErrorOutput());
            }

            //修正csv路径位置为临时目录
            $csv_path = $this->getSvnCachePath();

            $output->writeln(
                [
                    'SVN导出数据表到本地完成',
                    '导出位置: ' . $csv_path
                ]
            );
        }


        $output->writeln('配置检测开始...');


        if (!$fs->exists($csv_path)) {
            $output->writeln('<error> csv 路径不存在:' . $csv_path . '</error>');
            return 1;
        }

        $csvFiles = utils::getFiles($csv_path, ['csv']);

        $errCount = 0;

        $checkOKMd5 = $this->getCachesMd5Content();

        foreach ($csvFiles as $csvFile) {

            if ($this->verboseDebug) {
                $output->writeln("<info>开始检测$csvFile</info>");
            }
            $filename = pathinfo($csvFile, PATHINFO_BASENAME);
            //上次检测成功的文件
            if (isset($checkOKMd5[$filename])) {
                $newMd5 = md5(file_get_contents($csvFile));
                //自从上次检测成功后,文件没有变化
                if ($newMd5 === $checkOKMd5[$filename]) {
                    continue;
                } else {
                    unset($checkOKMd5[$filename]);
                }
            }
            //开始检测
            $checkResults = utils::parseGameCsvData($csvFile, true, true, utils::exportCsvConfig_Mode_Check);

            if (!empty($checkResults)) {

                foreach ($checkResults as $message) {
                    $output->writeln('<error> ' . $errCount . ':[' . $filename . "]" . $message . '</error>');
                    $errCount++;
                }
            } else {
                //没有错误,增加到没有错误的检测列表中
                $checkOKMd5[$filename] = md5(file_get_contents($csvFile));
            }
        }

        $this->saveCachesMd5Content($checkOKMd5);

        $output->writeln('<info>检测完成,共计错误:' . $errCount . '个</info>');

        return $errCount;


    }

    /**
     * 获取文件缓存内容
     * @return array
     */
    private function getCachesMd5Content()
    {
        $path = utils::getCacheDirectoryPath() . 'CheckCsv.Caches';
        $md5s = [];
        if (file_exists($path)) {
            $md5s = unserialize(file_get_contents($path));
        }
        return $md5s;
    }

    /**
     * @param array $md5s
     */
    private function saveCachesMd5Content(array $md5s)
    {
        $path = utils::getCacheDirectoryPath() . 'CheckCsv.Caches';
        file_put_contents($path, serialize($md5s));
    }


}