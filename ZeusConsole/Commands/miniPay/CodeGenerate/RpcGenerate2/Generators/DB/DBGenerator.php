<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 20/03/2018
 * Time: 4:14 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB;


use Carbon\Carbon;
use PHPSQLParser\PHPSQLParser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\Base\Generator;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\Parse\ParseTable;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\RpcGenerate2;

/**
 * Class DBGenerator
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators
 */
class DBGenerator implements Generator
{

    /**
     * @var $exportPath string 导出路径
     */
    protected $exportPath;
    /**
     * @var
     */
    protected $mainClass;

    /**
     * @return RpcGenerate2
     */
    public function getMainClass()
    {
        return $this->mainClass;
    }

    /**
     * @param RpcGenerate2 $mainClass
     */
    public function setMainClass($mainClass)
    {
        $this->mainClass = $mainClass;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function generate(InputInterface $input, OutputInterface $output)
    {
        $templatePath = $input->getOption('templatePath');
        $this->exportPath = $input->getOption('exportPath');

        $finder = new Finder();
        $iterator = $finder->files()
            ->name('*.sql')
            ->depth('<10')
            ->in($templatePath);

        $output->writeln([
            "<info>开始生成数据代码文件....</info>"

        ]);


        $fileCount = 0;
        $errorCount = 0;
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }


            if ($this->generateCode($file, $output)) {
                $fileCount++;
            } else {
                $errorCount++;
            }
        }


        $output->writeln("<info>" . Carbon::now()->toDateTimeString() . "===>生成完毕,共生成数据库文件:$fileCount 个</info>");
    }


    /**
     * 生成代码
     * @param SplFileInfo $file
     * @param OutputInterface $output
     * @return false|string
     */
    private function generateCode(SplFileInfo $file, OutputInterface $output)
    {
        $relativePath = $file->getRelativePath();
        $database = $relativePath;

        $parser = new PHPSQLParser($file->getContents());
        if (empty($parser)) {
            return false;
        }
        $table = ParseTable::parse($parser->parsed['TABLE']);
        $table->setDatabase($database);

        $generatorClass = new DBGeneratorClass();
        $generatorClass->setTable($table);
        $generatorClass->setExportPath($this->exportPath);
        $generatorClass->setMainClass($this->getMainClass());

        $generatorClass->dumpTable();
        return true;
    }


}