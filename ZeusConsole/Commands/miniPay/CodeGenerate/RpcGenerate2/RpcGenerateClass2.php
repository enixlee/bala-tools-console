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
use Symfony\Component\Templating\PhpEngine;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\RpcInputParameter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\RpcOutputParameter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\ParameterTypeTemplate;

class RpcGenerateClass2
{

    private $nameSpace;
    private $className;
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


    private $description;
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
     * 接口是否废弃
     * @var bool
     */
    private $deprecated = false;

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
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
    public function getNameSpace()
    {
        return $this->nameSpace;
    }

    /**
     * @param mixed $nameSpace
     */
    public function setNameSpace($nameSpace)
    {
        $this->nameSpace = $nameSpace;
    }


    /**
     * 导出配置
     * @var RpcGenerateConfig
     */
    private $generatorConfig;

    /**
     * @param RpcGenerateConfig $generatorConfig
     */
    public function setGeneratorConfig($generatorConfig)
    {
        $this->generatorConfig = $generatorConfig;
    }

    /**
     * @return RpcGenerateConfig
     */
    public function getGeneratorConfig()
    {
        return $this->generatorConfig;
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
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
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
     * @return array|null
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


    public function fromArray(array $arr)
    {
//        $this->className = isset($arr['className']) ? $arr['className'] : null;
        $this->description = isset($arr['description']) ? $arr['description'] : null;
        $this->routeUrl = isset($arr['routeUrl']) ? $arr['routeUrl'] : null;
        $this->method = isset($arr['method']) ? $arr['method'] : null;
        $this->rpcType = isset($arr['rpcType']) ? $arr['rpcType'] : null;
        $this->rpcBridge = isset($arr['rpcBridge']) ? boolval($arr['rpcBridge']) : false;
        $this->deprecated = isset($arr['deprecated']) ? boolval($arr['deprecated']) : false;

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
//            $errorCode->setCode($errorCodeData['code']);
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

        //处理options

        $this->options = $arr['options'] ?? [];

    }

    protected $options = [];

    /**
     * 获取扩展项
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getOption($key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $exportPath
     * @param $namespace
     * @param null $fileName
     * @return string
     */
    protected function getExportPath($exportPath, $namespace, $fileName = null)
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

        dumpLine($this->getRpcTypeConfig());
        //RPC的命名空间
        $subNamespace = "RPC";
//        $filePath =  $exportPath . DIRECTORY_SEPARATOR . $subNamespace . DIRECTORY_SEPARATOR . $this->getClassName() . ".php";
        $filePath = $this->getExportPath($exportPath, "RPC", $this->getClassName() . ".php");
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
        $exportPath = $this->getExportPath($exportPath, $subNamespace);

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

        $filePath = $this->getExportPath($exportPath, "Tests", $this->getClassTestName() . ".php");
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
        $filePath = $this->getExportPath($exportPath, "Errors", "Error" . $this->getClassName() . ".php");
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


}