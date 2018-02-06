<?php
/**
 * Created by PhpStorm.
 * User: enixlee
 * Date: 2017/5/31
 * Time: 下午9:03
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\VueCodeGenerate;


class VueRpcMapGenerate
{
    protected $files = [];
    protected $rpcConfigs = [];

    public function addRpcConfigs($rpcConfig, $rpcFileName)
    {
        $this->rpcConfigs[] = [
            'config' => $rpcConfig,
            'fileName' => $rpcFileName
        ];

        $this->files[] = $rpcFileName;
    }

    function getNames()
    {
        $idx = 0;
        $count = count($this->files);
        $names = [];

        foreach ($this->files as $f) {
            $suffix = ',';
            $idx++;
            if ($idx == $count) {
                $suffix = '';
            }
            $names[] = '  ' . $f . ': ' . $f . '.' . $f . $suffix;
        }
        return $names;
    }


    public function getRpcTypesImport()
    {
        $imports = [];
        foreach ($this->rpcConfigs as $f) {
            $name = $f['fileName'];
            $imports[] = "import * as " . $name . " from './" . $name . "';";
        }
        return $imports;
    }

    public function getMap()
    {
        $maps = [];
        foreach ($this->rpcConfigs as $f) {
            $name = $f['fileName'];

            $rpc = "RpcMap[" . $name . "." . $name . "Method] = {\n";
            $rpc .= "  rpc: " . $name . "." . $name . ",\n";
            $rpc .= "  method: " . $name . "." . $name . "Method,\n";
            $rpc .= "  type: " . $name . "." . $name . "RpcType,\n";
            $rpc .= "  params: " . $this->getParamsArray($f['config']);
            $rpc .= "};";

            $maps[] = $rpc;
        }

        return $maps;
    }

    protected function getParamsArray($config)
    {
        $map = "[\n";
        $params = isset($config['parameters']) ? $config['parameters'] : [];
        $count = count($params);
        $idx = 0;

        foreach ($params as $parameter) {
            $suffix = ',';
            $idx++;
            if ($idx == $count) {
                $suffix = '';
            }
            $map .= "    '" . $parameter['name'] . "'" . $suffix . "\n";
        }
        $map .= "  ]\n";
        return $map;
    }
}