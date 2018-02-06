<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/9/13
 * Time: 上午10:53
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate;


class RpcGenerateClass
{
    private $nameSpace;
    private $className;
    private $classTestName;
    private $description;
    private $routeUrl;
    private $method;
    private $rpcType;
    private $functionName;

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
     * @var
     */
    private $generatorConfig;

    /**
     * @param mixed $generatorConfig
     */
    public function setGeneratorConfig($generatorConfig)
    {
        $this->generatorConfig = $generatorConfig;
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
     * @param RpcGenerateParameter[] $parameters
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
     * @return mixed
     */
    public function getRpcType()
    {
        return $this->rpcType;
    }


    /**
     * @var RpcGenerateParameter []
     */
    private $parameters = [];

    /**
     * @return RpcGenerateParameter[]
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


    public function fromArray(array $arr)
    {
        $this->className = isset($arr['className']) ? $arr['className'] : null;
        $this->description = isset($arr['description']) ? $arr['description'] : null;
        $this->routeUrl = isset($arr['routeUrl']) ? $arr['routeUrl'] : null;
        $this->method = isset($arr['method']) ? $arr['method'] : null;
        $this->rpcType = isset($arr['rpcType']) ? $arr['rpcType'] : null;


        $parameters = isset($arr["parameters"]) ? $arr["parameters"] : [];

        foreach ($parameters as $parameter) {
            $this->parameters[] = RpcGenerateParameter::createFromArray($parameter, $this->generatorConfig);
        }
    }
}