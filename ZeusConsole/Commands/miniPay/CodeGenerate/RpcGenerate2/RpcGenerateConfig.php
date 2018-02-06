<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/10/17
 * Time: 下午4:40
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2;

/**
 * 导出配置
 * Class RpcGenerateConfig
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2
 */
class RpcGenerateConfig
{
    /**
     *  原始配置
     * @var array
     */
    private $originConfig;

    /**
     * RpcGenerateConfig constructor.
     * @param $originConfig
     */
    public function __construct($originConfig)
    {
        $this->originConfig = $originConfig;
    }

    public function getOriginConfig()
    {
        return $this->originConfig;
    }


    /**
     * Rpc分类
     * @return mixed
     */
    public function getConfigRpcTypes()
    {
        return $this->originConfig['rpcTypes'];
    }

    /**
     * 获取RPCType设置
     * @param $typeName
     * @return null|array
     */
    public function getRpcTypeConfig($typeName)
    {
        $configs = $this->getConfigRpcTypes();
        foreach ($configs as $config) {
            if (strtolower($config['name']) == strtolower($typeName)) {
                return $config;
            }
        }
        return null;
    }
}