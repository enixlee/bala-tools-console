<?php
/**
 * Created by PhpStorm.
 * User: enixlee
 * Date: 2017/3/7
 * Time: 上午11:12
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\VueCodeGenerate;

use ZeusConsole\Commands\miniPay\CodeGenerate\VueCodeGenerate\CodeTemplates\VueRpcGenerateParameter;

class VueRpcGenerateClass
{
    protected $urlName = null;
    protected $route = null;
    protected $method = null;
    protected $desc = "";
    protected $params = [];
    protected $generatorConfig = null;
    protected $rpcType = null;
    protected $deprecated = false;

    function addTemplateParams($urlNameKey, $routeUrl, $method, $rpcType, $description, $params, $deprecated = false)
    {
        $this->urlName = $urlNameKey;
        $this->route = $routeUrl;
        $this->method = $method;
        $this->rpcType = $rpcType;
        $this->deprecated = $deprecated;

        $this->desc = "";
        if (!is_null($description)) {
            $description = str_replace(["\n", "\r", "\r\n", "\t", " "], "", $description);
            $this->desc = $description;
        }

        $this->params = [];
        if (!is_null($params)) {
            foreach ($params as $parameter) {
                $obj = VueRpcGenerateParameter::createFromArray($parameter, $this->generatorConfig);
                $this->params[] = $obj;
            }
        }

    }

    /**
     * @param mixed $config
     */
    public function setGeneratorConfig($config)
    {
        $this->generatorConfig = $config;
    }

    function getRoute()
    {
        return $this->route;
    }

    function getMethod()
    {
        return $this->method;
    }

    function description()
    {
        return $this->desc;
    }

    function getParams()
    {
        return $this->params;
    }

    function fileName()
    {
        return 'Rpc' . $this->urlName;
    }

    public function getParameterDeclares()
    {
        $declares = [];

        foreach ($this->params as $parameter) {
            $declare = $parameter->getParameterDeclareString();
            if (!is_null($declare)) {
                $declares[] = '  ' . $declare;
            }
        }
        return $declares;
    }

    public function getParameterCheck()
    {
        $declares = [];

        foreach ($this->params as $parameter) {
            $check = $parameter->getParameterTypeCheckString();
            if (!is_null($check)) {
                $declares[] = "  " . $check;
            }

        }
        return $declares;
    }

    public function checkParameterNull()
    {
        $declares = [];

        foreach ($this->params as $parameter) {
            $name = $parameter->getName();
            $declares[] = '  if (!lodash.isNull(' . $name . ') && !lodash.isUndefined(' . $name . ')) {' . "\n" . "    params['" . $name . "'] = " . $name . ";\n  }";
        }
        return $declares;
    }

    public function getParameterDocuments()
    {
        $declares = [];

        foreach ($this->params as $parameter) {
            $declares[] = ' ' . $parameter->getParameterDocument();
        }
        return $declares;
    }

    public function getRpcType()
    {
        return $this->rpcType;
    }
}