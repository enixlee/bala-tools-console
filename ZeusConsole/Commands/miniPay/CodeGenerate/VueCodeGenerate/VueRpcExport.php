<?php

/**
 * Created by PhpStorm.
 * User: enixlee
 * Date: 2017/3/7
 * Time: 上午10:45
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\VueCodeGenerate;

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

class VueRpcExport extends CommandBase
{
    protected function configure()
    {
        $this->setName('codeGenerate:vueRpcExport')
            ->setDescription('vue rpc 接口代码生成');

        $this->addOption('templateVueRpcPath', null, InputOption::VALUE_OPTIONAL, "模板源路径",
            $this->getTemplatePath());
        $this->addOption('exportVueRpcPath', null, InputOption::VALUE_OPTIONAL, "vue接口模板导出路径",
            $this->getExportJsApiPath());
        $this->addOption('useFullPath', null, InputOption::VALUE_OPTIONAL, "导出文件名是否使用全路径",
            $this->getUseFullPath());
    }

    /**
     * 获取文件数据模板路径
     */
    private function getTemplatePath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.templateVueRpcPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'templateVueRpcPath';
        }
        return $path;
    }

    /**
     * 获取导出路径
     * @return array|null|string
     */
    private function getExportJsApiPath()
    {
        $path = getConfig('miniPay.codeGenerate.rpcGenerate.exportVueRpcPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'exportVueRpcPath';
        }
        return $path;
    }

    private function getUseFullPath()
    {
        return false;
    }

    private $exportPath;
    private $useFullName = false;
    private $errorMsg = [];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templatePath = $input->getOption('templateVueRpcPath');
        $this->exportPath = $input->getOption('exportVueRpcPath');
        $this->useFullName = $input->getOption('useFullPath');

        //清理目标路径
        $fs = new Filesystem();
        $output->writeln($this->exportPath);
        $fs->remove($this->exportPath);
        $fs->mkdir($this->exportPath);

        $paths = explode('@', $templatePath);
        $files = [];
        foreach ($paths as $path) {
            $finder = new Finder();
            $iterator = $finder->files()
                ->name('*.yaml')
                ->depth('<10')
                ->in($path);
            foreach ($iterator as $file) {
                if (!$file instanceof SplFileInfo) {
                    continue;
                }
                $files[] = $file;
            }
        }

        $this->_execute($files, $output, $fs);
    }

    private function _execute($filesInTemplate, $output, $fs)
    {
        $fileCount = 0;
        $errorCount = 0;

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%");
        $template = new PhpEngine(new TemplateNameParser(), $loader);

        $GenerateClass = new VueRpcGenerateClass();
        $mapGenerate = new VueRpcMapGenerate();
        $GenerateClass->setGeneratorConfig(null);

        $rpcFiles = [];
        foreach ($filesInTemplate as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            $relativePath = $file->getRelativePathname();
            $absolute = $file->getPath();
            $pos = strpos($absolute, '/Rpc/');
            $path = substr($absolute, $pos + 5);
            $fullNamePre = explode("/", $path)[0];
            $yamlContent = Yaml::parse($file->getContents());
            $ClassNameParts = explode("/", ucwords($relativePath));

            $urlNameKeyPre = $this->useFullName ? $fullNamePre : '';
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
                $rpcType = $yamlContent['rpcType'];
                $params = isset($yamlContent['parameters']) ? $yamlContent['parameters'] : null;
                $deprecated = isset($yamlContent['deprecated']) ? $yamlContent['deprecated'] : false;
                if ($deprecated) {
                    continue;
                }

                $GenerateClass->addTemplateParams($urlNameKey, $routeUrl, $method, $rpcType, $description, $params, $deprecated);

                //写入文件
                try {
                    $filePath = $this->exportPath . DIRECTORY_SEPARATOR . 'Rpc' . $urlNameKey . '.js';
                    $errorCodeFilePath = $this->exportPath . DIRECTORY_SEPARATOR . 'ErrorCode' . DIRECTORY_SEPARATOR . 'ErrorRpc' . $urlNameKey . '.js';

                    $templated = $template->render("VueRpcTemplate.php", [
                        'generateClass' => $GenerateClass,
                    ]);
                    $templatedErrorCode = $template->render("VueErrorCodeTemplate.php", [
                        'generateClass' => $GenerateClass,
                    ]);
                    $output->writeln("Generator:$filePath");
                    $fs->dumpFile($filePath, $templated);
                    $fs->dumpFile($errorCodeFilePath, $templatedErrorCode);

                    //   var_dump($templated);

                } catch (IOException $e) {
                    $this->errorMsg[] = "<error>写入文件错误</error>";
                }
                $rpcFiles[] = $GenerateClass->fileName();

                $mapGenerate->addRpcConfigs($yamlContent, $GenerateClass->fileName());

//                $output->writeln($urlNameKey . ':' . ' CENTER_SERVER + ' . '"' . $routeUrl . '",' . '    \\\\' . $description);

            } catch (RpcGenerateParserError $e) {
                $this->errorMsg[] = "<error>YAML解析错误:" . $file->getPathname() . " </error>";
                $this->errorMsg[] = "<error>错误信息 无效的类型或者字段:" . $e->getMessage() . "</error>";
                $errorCount++;
            }

            $fileCount++;
        }

        if (count($rpcFiles) > 0) {
            $indexGenerate = new VueIndexGenerate();
            $indexGenerate->setFiles($rpcFiles);

            try {
                $errorCodeIndexPath = $this->exportPath . DIRECTORY_SEPARATOR . 'ErrorCode' . DIRECTORY_SEPARATOR . 'index.js';

                $errorCodeIndex = $template->render("VueErrorCodeIndexTemplate.php", [
                    'generateClass' => $indexGenerate,
                ]);
                $fs->dumpFile($errorCodeIndexPath, $errorCodeIndex);

                //   var_dump($templated);

            } catch (IOException $e) {
                $this->errorMsg[] = "<error>写入index.js文件错误</error>";
            }

            try {
                $filePath = $this->exportPath . DIRECTORY_SEPARATOR . 'RpcMap.js';

                $templated = $template->render("VueRpcMapTemplate.php", [
                    'generateClass' => $mapGenerate,
                ]);
                $output->writeln("Generator:$filePath");
                $fs->dumpFile($filePath, $templated);

                //   var_dump($templated);

            } catch (IOException $e) {
                $this->errorMsg[] = "<error>RpcMap.js文件错误</error>";
            }
        }

        $output->writeln("<info>生成完毕,共检测RPC文件:$fileCount 个</info>");
        if ($errorCount !== 0) {
            $output->writeln($this->errorMsg);
            $output->writeln("<error>错误:$errorCount 个</error>");
        }
    }
}