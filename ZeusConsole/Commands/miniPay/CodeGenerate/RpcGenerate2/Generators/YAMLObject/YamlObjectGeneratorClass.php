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
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError;
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

    protected $extends = null;

    /**
     * @return null
     */
    public function getExtends()
    {
        return $this->extends;
    }


    /**
     * @param array $arr
     * @throws \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError
     */
    public function fromArray(array $arr)
    {
        parent::fromArray($arr);

        $this->extends = $arr["extends"] ?? null;

        $returnParameters = $arr['parameters'] ?? [];
        $parameterTypeTemplate = new ParameterTypeTemplate();
        foreach ($returnParameters as $parameter) {
            $ins = new ObjectParameter($parameterTypeTemplate);
            $ins->fillDatas($parameter);
            $this->parameters[] = $ins;
        }


    }

    /**
     * @throws \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError
     */
    public function checkError()
    {
        parent::checkError();
        foreach ($this->parameters as $parameter) {
            $parameter->checkError();
        }
    }

    protected function dumpObject(string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output)
    {
        $filePath = "{$exportPath}/{$this->getClassName()}.php";
        $renderTemplate = $template->render("Object.php", [
            'generateClass' => $this,
        ]);
//
        $fs->dumpFile($filePath, $renderTemplate);
        return true;
    }


    public function generateCode(SplFileInfo $file, OutputInterface $output)
    {

        $yamlContent = Yaml::parse($file->getContents());

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%");
        $template = new PhpEngine(new TemplateNameParser(), $loader);

        try {
            $this->fromArray($yamlContent);
            $this->checkError();
        } catch (RpcGenerateParserError $e) {
            $errorMsg = [];
            $errorMsg[] = "<error>YAML Object 解析错误:" . $file->getPathname() . "</error>";
            $errorMsg[] = "<error>错误信息 无效的类型或者字段:" . $e->getMessage() . "</error>";
            $output->writeln($errorMsg);
            return self::ReturnFailed;
        }

        $className = ucfirst($file->getBasename(".yaml"));
        $this->setClassName($className);

        $nameSpace = $this->getExportNameSpace() . "\\objects";
        $this->setNameSpace($nameSpace);

        //导出路径增加命名空间
        $nameSpaceExportPath = "src" . DIRECTORY_SEPARATOR .
            str_replace("\\", DIRECTORY_SEPARATOR, $nameSpace);

        $this->dumpObject($nameSpaceExportPath, $template, new Filesystem(), $output);

        return self::ReturnSuccess;
    }


}