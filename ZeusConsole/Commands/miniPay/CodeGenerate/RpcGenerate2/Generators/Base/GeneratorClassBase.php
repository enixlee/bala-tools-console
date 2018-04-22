<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 7:00 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\Base;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\RpcGenerateConfig;

abstract class GeneratorClassBase implements GeneratorClass
{
    /**
     * @var string
     */
    protected $nameSpace;
    /**
     * @var string
     */
    protected $className;
    /**
     * @var string
     */
    protected $yamlType;
    /**
     * @var int
     */
    protected $version;
    /**
     * @var bool
     */
    protected $deprecated;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var string
     */
    protected $description;

    /**
     * 获取导出的命名空间
     * @return string
     */
    public function getExportNameSpace()
    {
        $nameSpace = getConfig('miniPay.codeGenerate.rpcGenerate2.NameSpace', "bala\codeTemplate");
        return rtrim($nameSpace);
    }

    /**
     * @return string
     */
    public function getNameSpace(): string
    {
        return $this->nameSpace;
    }

    /**
     * @param string $nameSpace
     */
    public function setNameSpace(string $nameSpace)
    {
        $this->nameSpace = $nameSpace;
    }


    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getYamlType(): string
    {
        return $this->yamlType;
    }

    /**
     * @param string $yamlType
     */
    public function setYamlType(string $yamlType)
    {
        $this->yamlType = $yamlType;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion(int $version)
    {
        $this->version = $version;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @param bool $deprecated
     */
    public function setDeprecated(bool $deprecated)
    {
        $this->deprecated = $deprecated;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

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
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function fromArray(array $arr)
    {
        $this->className = $arr['className'] ?? null;
        $this->yamlType = $arr['yamlType'] ?? self::YAML_TYPE_RPC;
        $this->version = $arr['version'] ?? 0;
        $this->deprecated = boolval($arr['deprecated'] ?? false);
        $this->options = $arr['options'] ?? [];
        $this->description = $arr['description'] ?? "";
    }

    public function checkError()
    {

    }

    /**
     * @var string
     */
    protected $exportPath;

    /**
     * @return string
     */
    public function getExportPath(): string
    {
        return $this->exportPath;
    }

    /**
     * @param string $exportPath
     */
    public function setExportPath(string $exportPath): void
    {
        $this->exportPath = $exportPath;
    }

    /**
     * 导出配置
     * @var RpcGenerateConfig
     */
    protected $generatorConfig;

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


}