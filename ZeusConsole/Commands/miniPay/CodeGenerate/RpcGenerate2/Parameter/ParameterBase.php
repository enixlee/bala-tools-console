<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/6/26
 * Time: 下午5:49
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError;

abstract class ParameterBase
{
    protected $originData;
    private $name = null;
    private $type = null;
    private $min = null;
    private $max = null;
    private $choice = null;
    private $default = null;
    private $require = true;
    private $comment = null;
    protected $repeated = false;


    /**
     * @var ParameterTypeTemplate
     */
    protected $typeTemplateConfig;

    /**
     * ParameterBase constructor.
     * @param ParameterTypeTemplate $typeTemplateConfig
     */
    public function __construct(ParameterTypeTemplate $typeTemplateConfig)
    {
        $this->typeTemplateConfig = $typeTemplateConfig;
    }

    /**
     * @return ParameterTypeTemplate
     */
    public function getTypeTemplateConfig()
    {
        return $this->typeTemplateConfig;
    }

    /**
     * @param ParameterTypeTemplate $typeTemplateConfig
     */
    public function setTypeTemplateConfig($typeTemplateConfig)
    {
        $this->typeTemplateConfig = $typeTemplateConfig;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param string $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @return string
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param string $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @return array
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * @param array $choice
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;
    }

    /**
     * 是否有默认值
     * @return bool
     */
    public function hasDefault()
    {
        return !is_null($this->default);
    }

    /**
     * @return string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return boolean
     */
    public function isRequire()
    {
        return $this->require;
    }

    /**
     * @param boolean $require
     */
    public function setRequire($require)
    {
        $this->require = boolval($require);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return bool
     */
    public function isRepeated(): bool
    {
        return $this->repeated;
    }

    protected const OBJ_PREFIX = "obj.";

    /**
     * @return bool
     */
    public function isObject()
    {
        return starts_with($this->getType(), self::OBJ_PREFIX);
    }


    /**
     * 是否是自定义消息
     * @return bool
     */
    public function isMessage()
    {
        return $this->getType() == 'message';
    }

    /**
     * 获取对象类名
     * @return null|string
     */
    public function getObjectTypeClassName()
    {
        if ($this->isObject()) {
            $declare = str_replace_first(self::OBJ_PREFIX, "", $this->type);
            return $declare;
        } else {
            return null;
        }
    }

    /**
     * 填充数据
     * @param $originDataArray
     * @throws RpcGenerateParserError
     */
    public function fillDatas($originDataArray)
    {
        $this->originData = [];
        foreach ($originDataArray as $key => $value) {
            if (empty(trim($key))) {
                throw new RpcGenerateParserError('有字段名为空');
            }
            $this->originData[trim($key)] = $value;
        }

        $this->name = isset($this->originData['name']) ? $this->originData['name'] : null;
        $this->type = isset($this->originData['type']) ? $this->originData['type'] : null;
        $this->min = isset($this->originData['min']) ? $this->originData['min'] : null;
        $this->max = isset($this->originData['max']) ? $this->originData['max'] : null;
        $this->choice = isset($this->originData['choice']) ? $this->originData['choice'] : null;
        $this->default = isset($this->originData['default']) ? $this->originData['default'] : null;
        $this->require = isset($this->originData['require']) ? $this->originData['require'] : false;
        $this->comment = isset($this->originData['comment']) ? $this->originData['comment'] : "";
        $this->repeated = boolval($this->originData['repeated'] ?? false);
    }

    /**
     * 检测参数错误
     * @throws RpcGenerateParserError
     */
    public function checkError()
    {
        if (is_null($this->name)) {
            $this->error("字段缺少name");
        }
        if (is_null($this->type)) {
            $this->error("字段{$this->name}:缺少type");
        }
    }

    /**
     * @param $message
     * @throws RpcGenerateParserError
     */
    protected function error($message)
    {
        throw new RpcGenerateParserError($message);
    }


    /**
     * 获取声明字符串
     * @return string
     */
    public function getTypeDeclareAsString()
    {
        return $this->getTypeTemplateConfig()->getTypeDeclareAsString($this->getType());
    }

    /**
     * 是否是字符串类型
     * @return bool
     */
    public function is_string()
    {
        return $this->getTypeTemplateConfig()->getTypeDeclareAsString($this->getType()) == ParameterTypeTemplate::PARAM_TYPE_STRING;
    }


}