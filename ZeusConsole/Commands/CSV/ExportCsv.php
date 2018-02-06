<?php

namespace ZeusConsole\Commands\CSV;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;
use utilphp\util;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Commands\System\dumpConfig;
use ZeusConsole\Utils\utils;

/**
 * Class ExportCsv
 * @package ZeusConsole\Commands\CSV
 */
class ExportCsv extends CommandBase
{
    protected function configure()
    {
        $this->setName('csv:export');
        $this->setDescription('导出csv表格');
        $this->addArgument('csv-path', InputArgument::REQUIRED, 'csv数据源路径,可以使svn路径');
        $this->addArgument('export-path', InputArgument::REQUIRED, 'csv数据导出到的位置');

        $this->addOption('export-format', 'ef', InputOption::VALUE_REQUIRED, '导出文件的格式 lua,json,php', 'php');
        $this->addOption('export-client', 'c', InputOption::VALUE_NONE, '导出客户端');
        $this->addOption('export-server', 's', InputOption::VALUE_NONE, '导出服务器数据');
        $this->addOption('delete', null, InputOption::VALUE_NONE, '删除目标导出目录中的导出文件');
        $this->addOption('csvRevision', null, InputOption::VALUE_OPTIONAL, "如果是SVN路径,从特殊的版本号打包,0为最新", 0);

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        var_dump($input->getArguments());
//        var_dump($input->getOptions());

        $CSVPath = $input->getArgument('csv-path');
        $fs = new Filesystem();

        $localCSVSVNPath = utils::getTempDirectoryPath() . 'csvexport';
        $fs->remove($localCSVSVNPath);
        $fs->mkdir($localCSVSVNPath);

        //导出文件格式 php or lua
        $exportFormat = $input->getOption('export-format');


        $configMd5Code = "";
        //是否是从SVN导出资源
        $isFromSvn = util::starts_with($CSVPath, 'svn://');
        $exportRevision = -1;
        if ($isFromSvn) {

            //获取导出版本号
            $newestVersion = utils::getSvnRevision($CSVPath);

            //导出的特定的svn版本号
            $exportRevision = $input->getOption("csvRevision");
            if (intval($exportRevision) === 0) {
                $exportRevision = $newestVersion;
            }
            //使用SVN版本号标记资源版本号
            $configMd5Code = "SVN_" . $exportRevision;

            $process = utils::createSvnProcess([
                'export',
                '--force',
                '-r',
                $exportRevision,
                $CSVPath,
                $localCSVSVNPath,
            ]);
            $process->run();
//            $output->writeln('<info>' . $process->getCommandLine() . '</info>');
//            $output->writeln('<info>' . $process->getErrorOutput() . '</info>');
//            $output->writeln('<info>' . $process->getOutput() . '</info>');

//            var_dump($newestVersion);
//            return 0;
            //完成后修改csv路径
            $CSVPath = $localCSVSVNPath;
        } else {
            if (!$fs->exists($CSVPath)) {
                $output->writeln('<error>CSV:路径错误:' . $CSVPath . '</error>');
                return 0;
            }
        }

        $exportPath = $input->getArgument('export-path');
        if (!$fs->exists($exportPath)) {
            $output->writeln('<error>CSV:导出路径错误路径错误:' . $exportPath . '</error>');
            return 0;
        }


        $exportServerConfig = $input->getOption('export-server');
        $exportClientConfig = $input->getOption('export-client');
        if (!$exportServerConfig && !$exportClientConfig) {
            $output->writeln('<error>没有导出参数 -c 或者 -s</error>');
            return 0;
        }

        if ($input->getOption("delete")) {
            $fs->remove($exportPath);
            $fs->mkdir($exportPath);

        }
        $csvFiles = utils::getFiles($CSVPath, ['csv']);
        if ($this->isVerboseDebug()) {
//            var_dump($csvFiles);
        }

//        return 0;
        $exportCount = 0;

        foreach ($csvFiles as $csvFile) {

            $baseName = pathinfo($csvFile, PATHINFO_FILENAME);
            $baseNameArr = explode('$', $baseName);
            if (count($baseNameArr) != 3) {
                continue;
            }
            //文件导出名称
            $baseNameExportName = $baseNameArr[1];
            //文件导出选项 cs c s
            $baseNameOptions = $baseNameArr[2];

            if (!$isFromSvn) {
                if (strpos($baseNameOptions, 's') !== false ||
                    strpos($baseNameOptions, 'c')
                ) {
                    //有效的导出文件,则记录到配置校验码中
                    $configMd5Code .= md5_file($csvFile);
                }
            }

            if ($exportServerConfig && strpos($baseNameOptions, 's') !== false) {
                $hasExportData = true;
            } elseif ($exportClientConfig && strpos($baseNameOptions, 'c') !== false) {
                $hasExportData = true;
            } else {
                $hasExportData = false;
            }

            if (!$hasExportData) {
                continue;
            }

            //倒数数据
            $CSVDatas = utils::parseGameCsvData($csvFile,
                $exportServerConfig,
                $exportClientConfig,
                utils::exportCsvConfig_Mode_Export);
            //导出标题
            $CSVDataTitle = utils::parseGameCsvDataTitle($csvFile,
                $exportServerConfig,
                $exportClientConfig);

            //最终导出的文件内容
            list($exportFileName, $exportCodeString) = $this->exportCode($exportFormat,
                $baseNameExportName,
                $CSVDataTitle,
                $CSVDatas,
                $exportServerConfig,
                $exportClientConfig);

            if (!empty($exportCodeString)) {
                $codePath = $exportPath . DIRECTORY_SEPARATOR . $exportFileName . "." . $exportFormat;
                $output->writeln('<info>正在导出:' . $codePath . '</info>');
                $fs->dumpFile($codePath, $exportCodeString);

                $exportCount++;
            }

        }
        if (!$isFromSvn) {
            var_dump([
                "配置总MD5:" => $configMd5Code,
                "配置MD5" => md5($configMd5Code)
            ]);
            $configMd5Code = md5($configMd5Code);
        }

        if (!empty($configMd5Code)) {
            //有资源版本校验码
            if ($this->verboseDebug) {
                var_dump([
                    "配置资源版本号" => $configMd5Code
                ]);
            }
            //构建资源版本数据
            $CSVResourceConfig = "csv_resources_setting";
            $CSVResourceConfigTitle = [
                'key$cs' => "key",
                'value$cs' => "value"
            ];
            $CSVResourceConfigDatas = [
                //资源版本号
                [
                    "key" => "ResourceCheckCode",
                    "value" => $configMd5Code
                ]
            ];
            if ($isFromSvn) {
                $CSVResourceConfigDatas[] = [
                    "key" => "SVNRevision",
                    "value" => $exportRevision
                ];
            }

            //最终导出的文件内容
            list($exportFileName, $exportCodeString) = $this->exportCode($exportFormat,
                $CSVResourceConfig,
                $CSVResourceConfigTitle,
                $CSVResourceConfigDatas,
                $exportServerConfig,
                $exportClientConfig);

            if (!empty($exportCodeString)) {
                $codePath = $exportPath . DIRECTORY_SEPARATOR . $exportFileName . "." . $exportFormat;
                $output->writeln('<info>正在导出资源控制文件:' . $codePath . '</info>');
                $fs->dumpFile($codePath, $exportCodeString);

                $exportCount++;
            }
        }
        $output->writeln('<info>导出完成,共导出:' . $exportCount . '个文件</info>');
        return 0;


    }

    /**
     * @param $exportFormat
     * @param $exportClassName
     * @param array $exportDataTitle 类似
     * [
     * 'key$cs' => "key",
     * 'value$cs' => "value"
     * ];
     *
     * @param array $exportDatas
     * @return array
     */
    private function exportCode($exportFormat, $exportClassName,
                                array $exportDataTitle, array $exportDatas, $exportServerCode = true, $exportClientCode = true)

    {
        $exportCodeString = "";
        $exportFileName = "";
        if ($exportFormat == 'php') {

            if ($exportClientCode) {
                //php中导出客户端文件,为了同步配置使用
                $namespace = "configdataClient";
            } else {
                $namespace = "configdata";
            }
            $exportFileName = $namespace . '_' . $exportClassName;
            $exportCodeString = $this->genPhpCode($namespace, $exportClassName, $exportDataTitle
                , $exportDatas);
        } elseif ($exportFormat == 'lua') {
            $exportFileName = $exportClassName;
            $exportCodeString = $this->genLuaCode($exportClassName,
                $exportDataTitle, $exportDatas);
        } else {
        }
        return [$exportFileName, $exportCodeString];
    }


    /**
     * 生成php代码
     * @param $nameSpace
     * @param $className
     * @param array $titles
     * @param array $datas
     * @return string
     */
    private function genPhpCode($nameSpace, $className, array $titles = [], array $datas = [])
    {
        $phpCode = "<?php\n";
        $phpCode .= "namespace $nameSpace;\n";
        $phpCode .= "/**\n";
        $phpCode .= "* gen by zeus.php\n";
        $phpCode .= "*/\n";
        $phpCode .= "class " . $nameSpace . "_" . $className . " {\n";
        foreach ($titles as $title) {
            $phpCode .= "const k_" . $title . " = \"" . $title . "\";\n";
        }
        $phpCode .= 'private static $_data = NULL;' . "\n";
        $phpCode .= 'public static function data() {' . "\n";
        $phpCode .= 'if (is_null ( self::$_data )) {' . "\n";
        $phpCode .= 'self::$_data = [' . "\n";

        //parseDatas

        foreach ($datas as $data) {


            $phpInlineCode = "[";
            foreach ($data as $exportKey => $exportValue) {
                $phpInlineCode .= "'" . $exportKey . "'=>" . '\'' . $exportValue . '\',';
            }
            $phpInlineCode = rtrim($phpInlineCode, ",");
            $phpInlineCode .= "]";

            $phpCode .= $phpInlineCode . ",\n";

        }

        $phpCode = rtrim($phpCode, ",\n") . "\n";

        $phpCode .= '];' . "\n";
        $phpCode .= '}' . "\n";
        $phpCode .= ' return self::$_data;' . "\n";
        $phpCode .= '}' . "\n";
        $phpCode .= '}' . "\n";

        return $phpCode;
    }

    /**
     * 导出lua数据格式
     * @param $className
     * @param array $titles
     * @param array $datas
     * @return string
     */
    private function genLuaCode($className, array $titles = [], array $datas = [])
    {
        $luaCode = "config_" . $className . "_key=" . "{}\n";
        foreach ($titles as $title) {
            $luaCode .= "config_" . $className . "_key['$title'] = " . "'$title'\n";
        }
        $tableName = "config_" . $className;
        $luaCode .= "local " . $tableName . " = {\n";
        $len = count($datas);
        $luaInlineCode = "";
        $i = 1;
        foreach ($datas as $data) {
            $luaInlineCode .= "[" . $i . "]={";
            foreach ($data as $exportKey => $exportValue) {
                $luaInlineCode .= $exportKey . "='" . $exportValue . "',";
            }
            $luaInlineCode = rtrim($luaInlineCode, ",\n");
            $luaInlineCode .= "},\n";
            $i++;
        }

        $luaInlineCode = rtrim($luaInlineCode, ",\n") . "\n";
        $luaCode .= $luaInlineCode . "}\nreturn " . $tableName;
        return $luaCode;
    }


}
