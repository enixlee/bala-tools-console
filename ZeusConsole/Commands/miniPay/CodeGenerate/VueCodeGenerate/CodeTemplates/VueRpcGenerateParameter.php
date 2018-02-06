<?php

/**
 * Created by PhpStorm.
 * User: enixlee
 * Date: 2017/3/7
 * Time: 下午3:08
 */
namespace ZeusConsole\Commands\miniPay\CodeGenerate\VueCodeGenerate\CodeTemplates;

use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate\Exceptions\RpcGenerateParserError;

class VueRpcGenerateParameter
{
    private $originData;
    private $name = null;
    private $type = null;
    private $min = null;
    private $max = null;
    private $choice = null;
    private $default = null;
    private $require = true;
    private $comment = null;

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param null $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return null
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param null $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @return null
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param null $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @return null
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * @param null $choice
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param null $default
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
        $this->require = $require;
    }

    /**
     * @return null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param null $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    private function setGeneratorConfig($config)
    {
        if (is_null($config)) {
            return;
        }
        //支持扩展配置
        if (isset($config['typeCheckExtends'])) {

            foreach ($config['typeCheckExtends'] as $typeCheck) {
                $typeName = strtolower($typeCheck['name']);
                $this->typeMap[$typeName] = [
                    "type" => $typeCheck['type'],
                    "typeCheck" => $typeName
                ];
                $this->typeCheckFunction[$typeName] = $typeCheck['check_template'];
            }
        }
    }

    private $typeMap = [
        'int' => [
            "type" => "int",
            "typeCheck" => "number"
        ],
        'string' => [
            "type" => "string"
        ],
        "datetime" => [
            "type" => "datetime",
            "typeCheck" => "datetime"
        ],
        "float" => [
            "type" => "float",
            "typeCheck" => "number"
        ],
        "json" => [
            "type" => "string",
            "typeCheck" => 'json'
        ],
        "cellphone" => [
            "type" => "string",
            "typeCheck" => "cellphone",
        ],
        "md5" => [
            "type" => "string",
            "typeCheck" => "md5",
        ],
        "md5_16" => [
            "type" => "string",
            "typeCheck" => "md5_16",
        ],
        "money" => [
            "type" => "int",
            "typeCheck" => "money",
        ],
        "money_cent" => [
            "type" => "int",
            "typeCheck" => "money_cent",
        ],
        "userid" => [
            "type" => "string",
            "typeCheck" => "userid",
        ],
        "guid" => [
            "type" => "string",
            "typeCheck" => "guid",
        ]

    ];

    private $typeCheckFunction = [
        'number' => "tc.typeCheckNumber({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        'string' => "tc.typeCheckString({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        'datetime' => "tc.typeCheckDateString({{ value }}, {{ nullEnable }})",
        'float' => "tc.typeCheckNumber({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        "json" => "tc.typeCheckJsonString({{ value }}, {{ nullEnable }})",
        "choice" => "tc.typeCheckChoice({{ value }}, {{ choices }}, {{ nullEnable }})",
        "jsonChoice" => "tc.typeCheckJsonArrayChoice({{ value }}, {{ choices }}, {{ nullEnable }})",
        "cellphone" => "tc.typeCheckCellphone({{ value }}, {{ nullEnable }})",
        "md5" => "tc.typeCheckMd5({{ value }}, {{ nullEnable }})",
        "md5_16" => "tc.typeCheckMd5OfLength16({{ value }}, {{ nullEnable }})",
        "money" => "tc.typeCheckNumber({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        "money_cent" => "tc.typeCheckNumber({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        "userid" => "tc.typeCheckUserId({{ value }}, null, 64, {{ nullEnable }})",
        "guid" => "tc.typeCheckGuid({{ value }}, 32, 64, {{ nullEnable }})",
    ];

    /**
     * 获取变量声明类型
     * @return string
     */
    protected function getTypeAlias()
    {
        $type = strtolower($this->type);
        if (isset($this->typeMap[$type])) {
            return $this->typeMap[$type]['type'];
        }
        return $type;
    }

    /**
     * 获取类型检测的关键字
     * @return string
     */
    protected function getTypeCheckKey()
    {
        $type = strtolower($this->type);
        if (!is_null($this->choice)) {
            if ($this->type == "json") {
                return "jsonChoice";
            } else {
                return "choice";
            }
        }

        if (isset($this->typeMap[$type])) {
            if (isset($this->typeMap[$type]["typeCheck"])) {
                return $this->typeMap[$type]['typeCheck'];
            }
        }
        return $type;
    }

    public function getParameterDocument()
    {
        $des = "* @param " . $this->getParameterVarString() . ($this->require ? "" : " |null") . " " . $this->type;
        if (!is_null($this->comment) && !empty($this->comment)) {
            $des .= " " . $this->comment;
        }

        return $des;
    }

    public function getParameterAsArrayKeyDescription()
    {
        return "//" . $this->comment;
    }

    public function getParameterAsArrayKey()
    {
        return '"' . strtolower($this->name) . '" => ' . ($this->isRequire() ? "true" : "null");
    }

    public function getParameterAsArrayKeyWithParameterVar()
    {
        return '"' . strtolower($this->name) . '" => ' . $this->getParameterVarString();
    }

    /**
     * 获取函数声明字段
     * @return string
     */
    public function getParameterDeclareString()
    {
        $declare = $this->getParameterVarString();

        if ($this->require) {
            if (!is_null($this->default)) {
                $declare .= " = " . $this->default;
            }
        } else {
            $declare .= " = " . (is_null($this->default) ? "null" : strval($this->default));
        }
        return $declare;
    }

    /**
     * 获取变量字符串
     * @return string
     */
    public function getParameterVarString()
    {
        return $this->name;
    }

    public function getParameterTypeCheckString()
    {
        $typeCheckKey = $this->getTypeCheckKey();
        $typeCheckTemplate = isset($this->typeCheckFunction[$typeCheckKey]) ? $this->typeCheckFunction[$typeCheckKey] : null;
        if (is_null($typeCheckTemplate)) {
            return null;
        }

//        var_dump($this->choice);

        return translator()->trans($typeCheckTemplate, [
                "{{ value }}" => $this->getParameterVarString(),
                "{{ min }}" => is_null($this->min) ? "null" : strval($this->min),
                "{{ max }}" => is_null($this->max) ? "null" : strval($this->max),
                "{{ nullEnable }}" => $this->require ? "false" : "true",
                "{{ choices }}" => is_null($this->choice) ? "null" : "[" . join(", ", $this->choice) . "]"
            ]) . ";";
    }


    public function fromArray(Array $array)
    {
        $this->originData = [];
        foreach ($array as $key => $value) {
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
        $this->require = isset($this->originData['require']) ? $this->originData['require'] : null;
        $this->comment = isset($this->originData['comment']) ? $this->originData['comment'] : null;
        if (!is_null($this->comment)) {
            $this->comment = str_replace(["\n", "\r", "\r\n", "\t", " "], "", $this->comment);
        }


//        $type = strtolower($this->type);
//        if (!isset($this->typeMap[$type])) {
//            throw new RpcGenerateParserError($type);
//        }

    }

    static function createFromArray(Array $array, array $generatorConf = null)
    {
        $ins = new self();
        $ins->setGeneratorConfig($generatorConf);
        $ins->fromArray($array);
        return $ins;
    }
}