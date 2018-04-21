<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/9/13
 * Time: 上午10:53
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2;


use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Yaml\Yaml;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\Base\GeneratorClassBase;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\RpcInputParameter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\RpcOutputParameter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\ParameterTypeTemplate;

class RpcGenerateClass2 extends GeneratorClassBase
{

    private $classTestName;
    /**
     * @var string RPC返回类名
     */
    private $rpcReturnParametersClassName;

    /**
     * @return string
     */
    public function getRpcReturnParametersClassName()
    {
        return $this->rpcReturnParametersClassName;
    }


    private $routeUrl;
    private $method;
    private $rpcType;
    private $functionName;
    /**
     * 是否生成bridge代码
     * @var bool
     */
    private $rpcBridge = false;

    /**
     * @return bool
     */
    public function isRpcBridge(): bool
    {
        return $this->rpcBridge;
    }


    /**
     * @var RpcOutputParameter []
     */
    private $returnParameters;

    /**
     * @return RpcOutputParameter[]
     */
    public function getReturnParameters()
    {
        return $this->returnParameters;
    }


    /**
     * @return mixed
     */
    public function getClassTestName()
    {
        return $this->classTestName;
    }


    /**
     * @return mixed
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }

    /**
     * @param mixed $functionName
     */
    public function setFunctionName($functionName)
    {
        $this->functionName = $functionName;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
        $this->classTestName = "$className" . "TestParameters";
        $this->rpcReturnParametersClassName = $className . "ReturnParameters";
    }


    /**
     * @param mixed $routeUrl
     */
    public function setRouteUrl($routeUrl)
    {
        $this->routeUrl = $routeUrl;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param mixed $rpcType
     */
    public function setRpcType($rpcType)
    {
        $this->rpcType = $rpcType;
    }

    /**
     * @param RpcInputParameter[] $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     * @return mixed
     */
    public function getRouteUrl()
    {
        return $this->routeUrl;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getRpcType()
    {
        return $this->rpcType;
    }

    /**
     * 获取Rpc类型配置
     * @param null $key
     * @param null $default
     * @return array|null|string
     */
    public function getRpcTypeConfig($key = null, $default = null)
    {
        $config = $this->generatorConfig->getRpcTypeConfig($this->rpcType);
        if (is_null($config) || is_null($key)) {
            return $config;
        }
        return $config[$key] ?? $default;
    }


    /**
     * @var RpcInputParameter []
     */
    private $parameters = [];

    /**
     * @return RpcInputParameter[]
     */
    public function getParameters()
    {
        return $this->parameters;


    }


    public function getParameterDeclares()
    {
        $declares = [];

        foreach ($this->parameters as $parameter) {
            $declares[] = "        " . $parameter->getParameterDeclareString();
        }
        return $declares;
    }

    public function getParameterTransfer()
    {
        $declares = [];

        foreach ($this->parameters as $parameter) {
            $declares[] = $parameter->getParameterVarString();
        }
        return $declares;
    }

    public function getParameterCheck()
    {
        $declares = [];

        foreach ($this->parameters as $parameter) {
            $declares[] = "        " . $parameter->getParameterTypeCheckString();
        }
        return $declares;
    }

    public function getParameterDocuments()
    {
        $declares = [];

        foreach ($this->parameters as $parameter) {
            $declares[] = "     " . $parameter->getParameterDocument();
        }
        return $declares;
    }

    public function getParameterAsArray()
    {
        $declares = [];

        foreach ($this->parameters as $parameter) {
            $declares[] = "            " . $parameter->getParameterAsArrayKey() . "," . $parameter->getParameterAsArrayKeyDescription();
        }
        return $declares;
    }

    /**
     *
     * @return array
     */
    public function getParameterAsArrayWithParameterVar()
    {
        $declares = [];

        foreach ($this->parameters as $parameter) {
            $declares[] = "            " . $parameter->getParameterAsArrayKeyWithParameterVar() . "," . $parameter->getParameterAsArrayKeyDescription();
        }
        return $declares;
    }


    /**
     * @var RpcGenerateErrorCode[]
     */
    private $errorCodes = [];

    /**
     * @return RpcGenerateErrorCode[]
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }


    /**
     * @param array $arr
     * @throws \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError
     */
    public function fromArray(array $arr)
    {
        //因为之前已经设置过了,
        $arr['className'] = $this->className;
        parent::fromArray($arr);

        $this->routeUrl = isset($arr['routeUrl']) ? $arr['routeUrl'] : null;
        $this->method = isset($arr['method']) ? $arr['method'] : null;
        $this->rpcType = isset($arr['rpcType']) ? $arr['rpcType'] : null;
        $this->rpcBridge = isset($arr['rpcBridge']) ? boolval($arr['rpcBridge']) : false;

        $parameterTypeTemplate = new ParameterTypeTemplate();
        $parameterTypeTemplate->setGeneratorExtendsConfig($this->generatorConfig->getOriginConfig());
        $parameters = isset($arr["parameters"]) ? $arr["parameters"] : [];

        foreach ($parameters as $parameter) {
            $ins = new RpcInputParameter($parameterTypeTemplate);
            $ins->fromArray($parameter);
            $this->parameters[] = $ins;
        }


        //处理错误码
        $errorCodes = isset($arr["errorCodes"]) ? $arr["errorCodes"] : [];
        foreach ($errorCodes as $errorCodeData) {
            $errorCode = new RpcGenerateErrorCode();
            $errorCode->setName($errorCodeData['name']);
            $errorCode->setCode(isset($errorCodeData['code']) ? $errorCodeData['code'] : $errorCodeData['name']);
            $errorCode->setComment($errorCodeData['comment']);
            $this->errorCodes[] = $errorCode;
        }

        $returnParameters = isset($arr['returnParameters']) ? $arr['returnParameters'] : [];
        if (!empty($returnParameters)) {
            foreach ($returnParameters as $parameter) {
                $ins = new RpcOutputParameter($parameterTypeTemplate);
                $ins->setMessageBaseClassName($this->getRpcReturnParametersClassName());
                $ins->fillDatas($parameter);
                $this->returnParameters[] = $ins;
            }
        }


    }


    /**
     * @param $exportPath
     * @param $namespace
     * @param null $fileName
     * @return string
     */
    protected function getRealExportPath($exportPath, $namespace, $fileName = null)
    {
        //RPC的命名空间
        $subNamespace = $namespace;
        $filePath = $exportPath . DIRECTORY_SEPARATOR . $subNamespace . DIRECTORY_SEPARATOR;
        if (!is_null($fileName)) {
            $filePath = $filePath . $fileName;
        }
        return $filePath;
    }

    /**
     * @param string $exportPath
     * @param PhpEngine $template
     * @param Filesystem $fs
     * @param OutputInterface $output
     * @param bool $isDebug
     * @return bool
     */
    public function dumpRPCLogicFiles(string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output, $isDebug = false)
    {

        //RPC的命名空间
//        $subNamespace = "RPC";
//        $filePath =  $exportPath . DIRECTORY_SEPARATOR . $subNamespace . DIRECTORY_SEPARATOR . $this->getClassName() . ".php";
        $filePath = $this->getRealExportPath($exportPath, "RPC", $this->getClassName() . ".php");
        $renderTemplate = $template->render("LogicTemplates.php", [
            'generateClass' => $this,
        ]);


        if ($isDebug) {
            $output->writeln("Generator Rpc:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);
        return true;
    }

    /**
     * @param string $exportPath
     * @param PhpEngine $template
     * @param Filesystem $fs
     * @param OutputInterface $output
     * @param bool $isDebug
     * @return bool
     */
    public function dumpReturnParameterFiles(string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output, $isDebug = false)
    {
        if (empty($this->returnParameters)) {
            return false;
        }

        //RPC的命名空间
        $subNamespace = "RPC";
        $exportPath = $this->getRealExportPath($exportPath, $subNamespace);

        $writeParameter = $this->returnParameters;
//        生成返回值中的message
        foreach ($this->returnParameters as $parameter) {
            if ($parameter->isMessage()) {
                $this->dumpReturnParameterMessageDataFiles($parameter, $exportPath, $template, $fs, $output, $isDebug);
            }
        }


        $renderTemplate = $template->render("LogicTemplatesReturnParameter.php", [
            'generateClass' => $this,
            'rpcOutputParameters' => $writeParameter
        ]);
        $filePath = $exportPath . $this->getRpcReturnParametersClassName() . ".php";

        if ($isDebug) {
            $output->writeln("Generator Return Parameter:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);
        return true;
    }

    /**
     * 生产自定义消息
     * @param RpcOutputParameter $parameter
     * @param string $exportPath
     * @param PhpEngine $template
     * @param Filesystem $fs
     * @param OutputInterface $output
     * @param bool $isDebug
     */
    private function dumpReturnParameterMessageDataFiles(RpcOutputParameter $parameter, string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output, $isDebug = false)
    {
        if (!$parameter->isMessage()) {
            return;
        }
        $renderTemplate = $template->render("LogicTemplatesReturnParameterMessage.php", [
                'generateClass' => $this,
                'rpcOutputParameter' => $parameter
            ]
        );
        $filePath = $exportPath . $parameter->getMessageClassName() . ".php";

        if ($isDebug) {
            $output->writeln("Generator Return Parameter:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);

        //递归子消息
        foreach ($parameter->getMessageData() as $subParameter) {
            /**
             * @var $parameter
             */
            if ($subParameter->isMessage()) {
                $this->dumpReturnParameterMessageDataFiles($subParameter, $exportPath, $template, $fs, $output, $isDebug);

            }
        }

    }

    /**
     * @param string $exportPath
     * @param PhpEngine $template
     * @param Filesystem $fs
     * @param OutputInterface $output
     * @param bool $isDebug
     * @return bool
     */
    public function dumpRpcBridgeFiles(string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output, $isDebug = false)
    {
        if (!$this->rpcBridge) {
            return false;
        }
        //生产Bridge代码
        $exportPath = $exportPath . DIRECTORY_SEPARATOR . "RpcBridge";

        $relativeExportPath = $this->getNameSpace() . DIRECTORY_SEPARATOR;

        $renderTemplate = $template->render("LogicBridgeTemplates.php", [
            'generateClass' => $this,
        ]);
        $filePath = $exportPath . DIRECTORY_SEPARATOR . $relativeExportPath . "RpcBridge" . $this->getClassName() . ".php";
        if ($isDebug) {
            $output->writeln("Generator RPC Bridge:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);
        return true;
    }

    /**
     * @param string $exportPath
     * @param PhpEngine $template
     * @param Filesystem $fs
     * @param OutputInterface $output
     * @param bool $isDebug
     * @return bool
     */
    public function dumpUnitTestFiles(string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output, $isDebug = false)
    {

        $filePath = $this->getRealExportPath($exportPath, "Tests", $this->getClassTestName() . ".php");
        $renderTemplate = $template->render("LogicTestTemplates.php", [
            'generateClass' => $this,
        ]);

        if ($isDebug) {
            $output->writeln("Generator Test Unit:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);
        return true;
    }

    /**
     * @param string $exportPath
     * @param PhpEngine $template
     * @param Filesystem $fs
     * @param OutputInterface $output
     * @param bool $isDebug
     * @return bool
     */
    public function dumpErrorCodeFiles(string $exportPath, PhpEngine $template, Filesystem $fs, OutputInterface $output, $isDebug = false)
    {
        if (empty($this->errorCodes)) {
            return false;
        }
        $filePath = $this->getRealExportPath($exportPath, "Errors", "Error" . $this->getClassName() . ".php");
//        $exportPath = $exportPath . DIRECTORY_SEPARATOR . "Errors";
        $renderTemplate = $template->render("LogicErrorTemplates.php", [
            'generateClass' => $this,
        ]);
//        $relativeExportPath = $this->getNameSpace() . DIRECTORY_SEPARATOR;
//        $filePath = $exportPath . DIRECTORY_SEPARATOR . $relativeExportPath . "Error" . $this->getClassName() . ".php";

        if ($isDebug) {
            $output->writeln("Generator Error Code Files:$filePath");
        }
        $fs->dumpFile($filePath, $renderTemplate);
        return true;
    }

    public function generateCode(SplFileInfo $file, OutputInterface $output)
    {
        $relativePath = $file->getRelativePath();
        $yamlContent = Yaml::parse($file->getContents());

        if (empty($yamlContent)) {
            return self::ReturnSuccess;
        }

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%");
        $template = new PhpEngine(new TemplateNameParser(), $loader);

        $ClassNameParts = explode("/", ucwords($relativePath));
        //首字母大写
        $ClassNameParts = array_map("ucfirst", $ClassNameParts);

        $ClassName = "Logic" . join("", $ClassNameParts) . ucfirst($file->getBasename(".yaml"));
        //yaml第一个文件夹,为命名空间
        $nameSpace = $this->getExportNameSpace() . "\\Logic" . $ClassNameParts[0];
        $functionName = ucfirst($file->getBasename(".yaml"));

        $this->setNameSpace($nameSpace);
        $this->setClassName($ClassName);
        $this->setFunctionName($functionName);

        try {
            $this->fromArray($yamlContent);
            $this->checkError();

        } catch (RpcGenerateParserError $e) {
            $errorMsg[] = "<error>YAML解析错误:" . $file->getPathname() . " </error>";
            $errorMsg[] = "<error>错误信息 无效的类型或者字段:" . $e->getMessage() . "</error>";
            $output->writeln($errorMsg);
            return self::ReturnFailed;
        }


        //导出路径增加命名空间
        $nameSpaceExportPath = $this->getExportPath() . DIRECTORY_SEPARATOR .
            str_replace("\\", DIRECTORY_SEPARATOR, $nameSpace);
        $fs = new Filesystem();

        $debugFlag = false;
        //生成RPC服务代码
        $this->dumpRPCLogicFiles($nameSpaceExportPath, $template, $fs, $output, $debugFlag);
        //生成返回值
        $this->dumpReturnParameterFiles($nameSpaceExportPath, $template, $fs, $output, $debugFlag);
        //生产测试用例代码
        $this->dumpUnitTestFiles($nameSpaceExportPath, $template, $fs, $output, $debugFlag);
        //错误Code
        $this->dumpErrorCodeFiles($nameSpaceExportPath, $template, $fs, $output, $debugFlag);
        return self::ReturnSuccess;
    }

    /**
     * @throws RpcGenerateParserError
     */
    public function checkError()
    {
        parent::checkError();

        foreach ($this->parameters as $parameter) {
            $parameter->checkError();
        }
        foreach ($this->returnParameters as $parameter) {
            $parameter->checkError();
        }
    }


}