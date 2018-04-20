<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 7:00 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\Base;


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
     * @return string
     */
    public function getDescription(): string
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


}