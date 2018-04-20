<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/6/15
 * Time: 下午6:01
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2;


use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Yaml\Yaml;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\DBGenerator;
use ZeusConsole\Utils\utils;

class RpcGenerate2 extends CommandBase
{
    protected function configure()
    {
        $this->setName('codeGenerate:rpcGenerate2')
            ->setDescription('rpc 接口代码生成,用命名空间组织生成类');

        $this->addOption('templatePath', null, InputOption::VALUE_OPTIONAL, "模板源路径",
            $this->getTemplatePath());
        $this->addOption('exportPath', null, InputOption::VALUE_OPTIONAL, "类导出路径",
            $this->getExportPath());
//        $this->addOption('exportTestsPath', null, InputOption::VALUE_OPTIONAL, "类测试用例参数导出路径",
//            $this->getExportTestsPath());
//        $this->addOption('exportBridgePath', null, InputOption::VALUE_OPTIONAL, "导出Rpc桥模板",
//            $this->getExportBridgePath());
    }


    /**
     * 获取文件数据模板路径
     */
    public function getTemplatePath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate2.TemplatePath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'TemplatePath';
        }
        return $path;
    }

    /**
     * 获取文件数据模板路径
     */
    public function getGenerateConfPath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate2.ConfigPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'ConfigPath';
        }
        return $path;
    }

    /**
     * 获取导出路径
     * @return array|null|string
     */
    public function getExportPath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate2.ExportPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'ExportPath';
        }
        return $path;
    }

    /**
     * 获取导出的命名空间
     * @return string
     */
    public function getExportNameSpace()
    {
        $nameSpace = getConfig('miniPay.codeGenerate.rpcGenerate2.NameSpace', "bala\codeTemplate");
        return rtrim($nameSpace);
    }


    private $exportPath;


    private $errorMsg = [];
    /**
     * 导出配置
     */
    private $exportConfig;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generateRpc($input, $output);


        $dbGenerator = new DBGenerator();
        $dbGenerator->setMainClass($this);
        $dbGenerator->generate($input, $output);

    }

    protected function generateRpc(InputInterface $input, OutputInterface $output)
    {
        $templatePath = $input->getOption('templatePath');
        $this->exportPath = $input->getOption('exportPath');

        $finder = new Finder();
        $iterator = $finder->files()
            ->name('*.yaml')
            ->depth('<10')
            ->in($templatePath);

        //清理目标路径
        $fs = new Filesystem();
        $fs->remove($this->exportPath);
        $fs->mkdir($this->exportPath);


        //加载导出配置
        $configPath = $this->getGenerateConfPath() . DIRECTORY_SEPARATOR . 'generate.yaml';
        if ($fs->exists($configPath)) {

            $this->exportConfig = Yaml::parse(file_get_contents($configPath));
        }

        $output->writeln([
            "<info>开始生成代码文件....</info>"

        ]);

        if ($this->isVerboseDebug()) {
            $output->writeln([
                'form:',
                $templatePath,
                'to:',
                $this->exportPath
            ]);
        }


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


        $output->writeln("<info>" . Carbon::now()->toDateTimeString() . "===>生成完毕,共生成RPC文件:$fileCount 个</info>");
        if ($errorCount !== 0) {
            $output->writeln($this->errorMsg);
            $output->writeln("<error>错误:$errorCount 个</error>");
        }


        //生成发布信息
        $buildInfo = [];
        $buildInfo['toolsVersion'] = getConfig('version');
        $buildFile = $this->exportPath . DIRECTORY_SEPARATOR . 'buildInformation.yaml';
        $fs->dumpFile($buildFile, Yaml::dump($buildInfo));
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
        $yamlContent = Yaml::parse($file->getContents());

        if (empty($yamlContent)) {
            return false;
        }

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%");
        $template = new PhpEngine(new TemplateNameParser(), $loader);

        $GenerateClass = new RpcGenerateClass2();
        $GenerateClass->setGeneratorConfig(new RpcGenerateConfig($this->exportConfig));


        $ClassNameParts = explode("/", ucwords($relativePath));
        //首字母大写
        $ClassNameParts = array_map("ucfirst", $ClassNameParts);

        $ClassName = "Logic" . join("", $ClassNameParts) . ucfirst($file->getBasename(".yaml"));
        //yaml第一个文件夹,为命名空间
        $nameSpace = $this->getExportNameSpace() . "\\Logic" . $ClassNameParts[0];
        $functionName = ucfirst($file->getBasename(".yaml"));


//        var_dump($nameSpace);
//        var_dump($functionName);

        $GenerateClass->setNameSpace($nameSpace);
        $GenerateClass->setClassName($ClassName);
        $GenerateClass->setFunctionName($functionName);

        try {
            $GenerateClass->fromArray($yamlContent);

        } catch (RpcGenerateParserError $e) {
            $this->errorMsg[] = "<error>YAML解析错误:" . $file->getPathname() . " </error>";
            $this->errorMsg[] = "<error>错误信息 无效的类型或者字段:" . $e->getMessage() . "</error>";
//            $output->writeln("<error>YAML解析错误:" . $file->getPathname() . "</error>");
            return false;
        }

        //导出路径增加命名空间
        $nameSpaceExportPath = $this->exportPath . DIRECTORY_SEPARATOR .
            str_replace("\\", DIRECTORY_SEPARATOR, $nameSpace);
//        dumpLine($nameSpaceExportPath);
        $fs = new Filesystem();

        //生成RPC服务代码
        $GenerateClass->dumpRPCLogicFiles($nameSpaceExportPath, $template, $fs, $output, $this->isVerboseDebug());


        //生成返回值
        $GenerateClass->dumpReturnParameterFiles($nameSpaceExportPath, $template, $fs, $output, $this->isVerboseDebug());

        //生产测试用例代码
        $GenerateClass->dumpUnitTestFiles($nameSpaceExportPath, $template, $fs, $output, $this->isVerboseDebug());

        //生成Bridge 代码
//        $GenerateClass->dumpRpcBridgeFiles($nameSpaceExportPath, $template, $fs, $output, $this->isVerboseDebug());
        //错误Code
        $GenerateClass->dumpErrorCodeFiles($nameSpaceExportPath, $template, $fs, $output, $this->isVerboseDebug());
        return true;


    }
}