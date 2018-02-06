<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/9/13
 * Time: 上午8:55
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate;


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
use ZeusConsole\Utils\utils;

class RpcGenerate extends CommandBase
{
    protected function configure()
    {
        $this->setName('codeGenerate:rpcGenerate')
            ->setDescription('rpc 接口代码生成');

        $this->addOption('templatePath', null, InputOption::VALUE_OPTIONAL, "模板源路径",
            $this->getTemplatePath());
        $this->addOption('exportPath', null, InputOption::VALUE_OPTIONAL, "类导出路径",
            $this->getExportPath());
        $this->addOption('exportTestsPath', null, InputOption::VALUE_OPTIONAL, "类测试用例参数导出路径",
            $this->getExportTestsPath());
        $this->addOption('exportBridgePath', null, InputOption::VALUE_OPTIONAL, "导出Rpc桥模板",
            $this->getExportBridgePath());
    }


    /**
     * 获取文件数据模板路径
     */
    private function getTemplatePath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.TemplatePath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'TemplatePath';
        }
        return $path;
    }

    /**
     * 获取文件数据模板路径
     */
    private function getGenerateConfPath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.ConfigPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'ConfigPath';
        }
        return $path;
    }

    /**
     * 获取导出路径
     * @return array|null|string
     */
    private function getExportPath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.ExportPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'ExportPath';
        }
        return $path;
    }

    /**
     * 获取导出路径
     * @return array|null|string
     */
    private function getExportTestsPath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.ExportTestsPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'ExportTestsPath';
        }
        return $path;
    }

    /**
     * 获取导出路径
     * @return array|null|string
     */
    private function getExportBridgePath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.ExportBridgePath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'ExportBridgePath';
        }
        return $path;
    }


    private $exportPath;
    private $exportTestsPath;
    private $exportBridgePath;
    private $errorMsg = [];
    /**
     * 导出配置
     */
    private $exportConfig;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templatePath = $input->getOption('templatePath');
        $this->exportPath = $input->getOption('exportPath');
        $this->exportTestsPath = $input->getOption('exportTestsPath');
        $this->exportBridgePath = $input->getOption('exportBridgePath');

        $finder = new Finder();
        $iterator = $finder->files()
            ->name('*.yaml')
            ->depth('<10')
            ->in($templatePath);

        //清理目标路径
        $fs = new Filesystem();
        $fs->remove($this->exportPath);
        $fs->mkdir($this->exportPath);
        $fs->remove($this->exportTestsPath);
        $fs->mkdir($this->exportTestsPath);
        $fs->remove($this->exportBridgePath);
        $fs->mkdir($this->exportBridgePath);


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


            if (!is_null($this->generateCode($file, $output))) {
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
    }

    private function generateCode(SplFileInfo $file, OutputInterface $output)
    {
        $relativePath = $file->getRelativePath();
        $yamlContent = Yaml::parse($file->getContents());

        if (empty($yamlContent)) {
            return null;
        }

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%");
        $template = new PhpEngine(new TemplateNameParser(), $loader);

        $GenerateClass = new RpcGenerateClass();
        $GenerateClass->setGeneratorConfig($this->exportConfig);

        try {
            $GenerateClass->fromArray($yamlContent);

        } catch (RpcGenerateParserError $e) {
            $this->errorMsg[] = "<error>YAML解析错误:" . $file->getPathname() . " </error>";
            $this->errorMsg[] = "<error>错误信息 无效的类型或者字段:" . $e->getMessage() . "</error>";
//            $output->writeln("<error>YAML解析错误:" . $file->getPathname() . "</error>");
            return null;
        }

        $ClassNameParts = explode("/", ucwords($relativePath));

        $ClassName = "Logic" . join("", $ClassNameParts) . $file->getBasename(".yaml");
        $GenerateClass->setNameSpace($ClassNameParts[0]);
        $GenerateClass->setClassName($ClassName);
        $GenerateClass->setFunctionName($file->getBasename(".yaml"));


        $fs = new Filesystem();

        //生成主要类代码
        $renderTemplate = $template->render("LogicTemplates.php", [
            'generateClass' => $GenerateClass,
        ]);
        $relativeExportPath = "Logic-" . $ClassNameParts[0] . DIRECTORY_SEPARATOR;
        $filePath = $this->exportPath . DIRECTORY_SEPARATOR . $relativeExportPath . $ClassName . ".php";
        if ($this->isVerboseDebug()) {
            $output->writeln("Generator:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);

        //生产测试用例代码
        $renderTemplate = $template->render("LogicTestTemplates.php", [
            'generateClass' => $GenerateClass,
        ]);
        $filePath = $this->exportTestsPath . DIRECTORY_SEPARATOR . $relativeExportPath . $GenerateClass->getClassTestName() . ".php";

        if ($this->isVerboseDebug()) {
            $output->writeln("Generator:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);

        if (isset($yamlContent['rpcBridge']) && $yamlContent['rpcBridge']) {

            //生产Bridge代码
            $renderTemplate = $template->render("LogicBridgeTemplates.php", [
                'generateClass' => $GenerateClass,
            ]);
            $filePath = $this->exportBridgePath . DIRECTORY_SEPARATOR . $relativeExportPath . "RpcBridge" . $GenerateClass->getClassName() . ".php";
            if ($this->isVerboseDebug()) {
                $output->writeln("Generator:$filePath");
            }
            $fs->dumpFile($filePath, $renderTemplate);
        }


        return $renderTemplate;


//        var_dump([
//            $relativePath,
////            $GenerateClass,
//            $templated
//        ]);
    }

}