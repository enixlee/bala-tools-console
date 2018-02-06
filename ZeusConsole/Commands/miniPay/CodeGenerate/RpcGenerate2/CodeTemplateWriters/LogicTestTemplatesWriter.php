<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/10/17
 * Time: 下午4:55
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter\RpcInputParameter;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\RpcGenerateClass2;

class LogicTestTemplatesWriter extends WriterBase
{


    /**
     * 服务返回值
     * @return string
     */
    public function writeServiceReturn()
    {

        switch ($this->generateClass->getRpcType()) {
            case "client":
                $returnString = 'hellaEngine\support\Http\HttpResponse';
                break;
            case "system":
                $returnString = 'hellaEngine\support\Http\HttpResponse';
                break;
            case "core":
                $returnString = 'Pluto\Foundation\RPC\RPCCommandResult';
                break;
            case "SDKDeveloper":
                $returnString = 'hellaEngine\support\Http\HttpResponse';
                break;
            case "merchantSdkClient":
                $returnString = 'hellaEngine\support\Http\HttpResponse';
                break;
            default:
                $returnString = 'hellaEngine\support\Http\HttpResponse';
                break;
        }
        return $returnString;
    }

    /**
     * 测试函数名称
     * @return string
     */
    public function getTestFunctionName()
    {
        $config = $this->generateClass->getRpcTypeConfig();
        if (!is_null($config) && isset($config['testFunctionName'])) {
            return $config['testFunctionName'];
        }

        switch ($this->generateClass->getRpcType()) {
            case "client":
                $returnString = "callServiceWithClientToken";
                break;
            case "system":
                $returnString = "callServiceWithSystemToken";
                break;
            case "customMerchant":
                $returnString = "callServiceWithSystemToken";
                break;
            case "core":
                $returnString = "callServiceWithRpcCommand";
                break;
            case "SDKDeveloper":
                $returnString = "callServiceWithSDKDeveloperToken";
                break;
            case "merchantSdkClient":
                $returnString = "callServiceWithMerchantSDKClientToken";
                break;
            default:
                $returnString = "undefined function:" . $this->generateClass->getRpcType();
                break;
        }
        return $returnString;
    }

    public function getTestParameters()
    {
        $params = [];
        foreach ($this->generateClass->getParameters() as $parameter) {
            /**
             * @var $parameter RpcInputParameter
             */
            $params [] = sprintf($this->format_tab_3 . "\"%s\" => $%s,//%s",
                $parameter->getName(),
                $parameter->getName(),
                $parameter->getComment());
        }

        return $params;
//        $this->generateClass->getParameterAsArray()
    }


}