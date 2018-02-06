<?php

/**
 * Created by PhpStorm.
 * User: enixlee
 * Date: 2016/12/15
 * Time: 下午5:40
 */
namespace ZeusConsole\Commands\miniPay\CodeGenerate\JSCodeGenerate;

use CFPropertyList\IOException;
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

class RpcJsUrlsExport extends CommandBase
{
    protected function configure()
    {
        $this->setName('codeGenerate:jsCodeGenerate')
            ->setDescription('rpc 接口代码生成');

        $this->addOption('templateJsPath', null, InputOption::VALUE_OPTIONAL, "模板源路径",
            $this->getTemplatePath());
        $this->addOption('exportJsApiUrlPath', null, InputOption::VALUE_OPTIONAL, "js接口描述导出路径",
            $this->getExportJsApiPath());
    }

    /**
     * 获取文件数据模板路径
     */
    private function getTemplatePath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.templateJsPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'TemplateJsPath';
        }
        return $path;
    }

    /**
     * 获取导出路径
     * @return array|null|string
     */
    private function getExportJsApiPath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.exportJsApiUrlPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'ExportJsApiUrlPath';
        }
        return $path;
    }

    private $exportPath;
    private $errorMsg = [];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templatePath = $input->getOption('templateJsPath');
        $this->exportPath = $input->getOption('exportJsApiUrlPath');

        $finder = new Finder();
        $iterator = $finder->files()
            ->name('*.yaml')
            ->depth('<10')
            ->in($templatePath);

        //清理目标路径
        $fs = new Filesystem();
        $output->writeln($this->exportPath);
        $fs->remove($this->exportPath);
        $fs->mkdir($this->exportPath);

        $fileCount = 0;
        $errorCount = 0;

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplate/%name%");
        $template = new PhpEngine(new TemplateNameParser(), $loader);

        $GenerateClass = new RpcGenerateJSClass();

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            $relativePath = $file->getRelativePathname();
            $yamlContent = Yaml::parse($file->getContents());
            $ClassNameParts = explode("/", ucwords($relativePath));

            $urlNameKeyPre = '';
            foreach ($ClassNameParts as $classNamePart) {
                $urlNameKeyPre .= $classNamePart;
            }

            if (empty($yamlContent)) {
                return null;
            }

            $urlNameKey = explode(".", $urlNameKeyPre)[0];

            try {
                $routeUrl = $yamlContent['routeUrl'];
                $description = $yamlContent['description'];
                $method = $yamlContent['method'];

                $GenerateClass->addUrl($urlNameKey, $routeUrl, $method ,$description);

                $output->writeln($urlNameKey . ':' . ' CENTER_SERVER + ' . '"' . $routeUrl . '",' . '    \\\\' . $description);

            } catch (RpcGenerateParserError $e) {
                $this->errorMsg[] = "<error>YAML解析错误:" . $file->getPathname() . " </error>";
                $this->errorMsg[] = "<error>错误信息 无效的类型或者字段:" . $e->getMessage() . "</error>";
                $errorCount++;
            }

            $fileCount++;
        }

        //写入文件
        try {
            $filePath = $this->exportPath . DIRECTORY_SEPARATOR . 'RpcUrlTemplate.js';

            $templated = $template->render("RpcUrlConfigTemplate.php", [
                'generateClass' => $GenerateClass,
            ]);
            $output->writeln("Generator:$filePath");
            $fs->dumpFile($filePath, $templated);

//            var_dump($templated);

        } catch (IOException $e) {
            $this->errorMsg[] = "<error>写入文件错误</error>";
        }

        $output->writeln("<info>生成完毕,共检测RPC文件:$fileCount 个</info>");
        if ($errorCount !== 0) {
            $output->writeln($this->errorMsg);
            $output->writeln("<error>错误:$errorCount 个</error>");
        }
    }

}