<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 5:46 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject;


use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Yaml\Yaml;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\Base\GeneratorClassBase;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\Parameter\ObjectParameter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\ParameterTypeTemplate;

class YamlObjectGeneratorClass extends GeneratorClassBase
{
    /**
     * @var ObjectParameter[]
     */
    protected $parameters;

    /**
     * @return ObjectParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
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

    public function fromArray(array $arr)
    {
        parent::fromArray($arr);
        $returnParameters = $arr['parameters'] ?? [];
        $parameterTypeTemplate = new ParameterTypeTemplate();
        foreach ($returnParameters as $parameter) {
            $ins = new ObjectParameter($parameterTypeTemplate);
            $ins->fillDatas($parameter);
            $this->parameters[] = $ins;
        }
    }

    protected function dumpObject(string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output)
    {
        $filePath = "{$exportPath}/{$this->getClassName()}.php";
//        dumpLine($filePath);
        $renderTemplate = $template->render("Object.php", [
            'generateClass' => $this,
        ]);

//        dumpLine($renderTemplate);
//
//        if ($isDebug) {
//            $output->writeln("Generator Test Unit:$filePath");
//        }
        $fs->dumpFile($filePath, $renderTemplate);
        return true;
    }


    public function generateCode(SplFileInfo $file, OutputInterface $output)
    {

//        $relativePath = $file->getRelativePath();
        $yamlContent = Yaml::parse($file->getContents());

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%");
        $template = new PhpEngine(new TemplateNameParser(), $loader);

        $this->fromArray($yamlContent);

        $className = ucfirst($file->getBasename(".yaml"));
        $this->setClassName($className);

        $nameSpace = $this->getExportNameSpace() . "\\objects";
        $this->setNameSpace($nameSpace);

        //导出路径增加命名空间
        $nameSpaceExportPath = "src" . DIRECTORY_SEPARATOR .
            str_replace("\\", DIRECTORY_SEPARATOR, $nameSpace);

        $this->dumpObject($nameSpaceExportPath, $template, new Filesystem(), $output);

        return 2;
    }


}