<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/6/26
 * Time: 下午5:48
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter;

/**
 * 类型模板
 * Class ParameterTypeTemplate
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Parameter
 */
class ParameterTypeTemplate
{
    /**
     * 参数基础类型String
     */
    const PARAM_TYPE_STRING = "string";
    /**
     * 参数基础类型int
     */
    const PARAM_TYPE_INT = "int";
    /**
     * 参数基础类型float
     */
    const PARAM_TYPE_FLOAT = "float";
    /**
     * bool指
     */
    const PARAM_TYPE_BOOL = "bool";

    /**
     * 参数类型
     */
    const TYPE_MAP_KEY_TYPE = "type";
    /**
     * 参数名称
     */
    const TYPE_MAP_KEY_NAME = "name";
    /**
     * 参数检测函数
     */
    const TYPE_MAP_KEY_TYPE_CHECK = "typeCheck";
    /**
     * 参数检测模板
     */
    const TYPE_MAP_KEY_TYPE_CHECK_TEMPLATE = "check_template";
    /**
     * 类型映射
     * @var array
     */
    private $typeMap = [
        'merchantno' => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING,
            "typeCheck" => "merchantno",
        ],
        'int' => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_INT
        ],
        'string' => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING
        ],
        "bigint" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING
        ],
        "datetime" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING
        ],
        "email" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING
        ],
        "float" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_FLOAT
        ],
        "json" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING
        ],
        "cellphone" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING,
            self::TYPE_MAP_KEY_TYPE_CHECK => "cellphone",
        ],
        "md5" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING,
            self::TYPE_MAP_KEY_TYPE_CHECK => "md5",
        ],
        "md5_16" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING,
            self::TYPE_MAP_KEY_TYPE_CHECK => "md5_16",
        ],
        "money" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_FLOAT,
            self::TYPE_MAP_KEY_TYPE_CHECK => "money",
        ],
        "money_cent" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_INT,
            self::TYPE_MAP_KEY_TYPE_CHECK => "money_cent",
        ],
        "number_verify_code" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING,
            self::TYPE_MAP_KEY_TYPE_CHECK => "number_verify_code",
        ],
        "id_card" => [
            self::TYPE_MAP_KEY_TYPE => self::PARAM_TYPE_STRING,
            self::TYPE_MAP_KEY_TYPE_CHECK => "id_card",
        ]

    ];

    /**
     * 类型检测函数
     * @var array
     */
    private $typeCheckFunction = [
        'merchantno' => "typeCheckMerchantNo({{ value }}, {{ nullEnable }})",
        'string' => "typeCheckString({{ value }}, {{ max }}, {{ min }}, {{ nullEnable }})",
        'int' => "typeCheckNumber({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        'bigint' => "typeCheckBigInt({{ value }}, {{ bigIntMin }}, {{ bigIntMax }}, {{ nullEnable }})",
        'datetime' => "typeCheckDateString({{ value }}, {{ nullEnable }})",
        'email' => "typeCheckEmail({{ value }}, {{ nullEnable }})",
        "json" => "typeCheckJsonString({{ value }}, {{ nullEnable }})",
        "choice" => "typeCheckChoice({{ value }}, {{ choices }}, {{ nullEnable }})",
        "json_choice" => "typeCheckJsonArrayChoice({{ value }}, {{ choices }}, {{ nullEnable }})",
        "float" => "typeCheckFloat({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        "cellphone" => "typeCheckCellphone({{ value }}, {{ nullEnable }})",
        "md5" => "typeCheckMd5({{ value }}, {{ nullEnable }})",
        "md5_16" => "typeCheckMd5_16({{ value }}, {{ nullEnable }})",
        "money" => "typeCheckMoney({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        "money_cent" => "typeCheckMoneyCent({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        "number_verify_code" => "typeCheckNumberVerifyCode({{ value }}, {{ min }}, {{ max }}, {{ nullEnable }})",
        "id_card" => "typeCheckIdCard({{ value }}, {{ nullEnable }})",
    ];

    public function setGeneratorExtendsConfig($config)
    {
        if (is_null($config)) {
            return;
        }
        //支持扩展配置
        if (isset($config['typeCheckExtends'])) {

            foreach ($config['typeCheckExtends'] as $typeCheck) {
                $typeName = strtolower($typeCheck[self::TYPE_MAP_KEY_NAME]);
                $this->typeMap[$typeName] = [
                    self::TYPE_MAP_KEY_TYPE => $typeCheck[self::TYPE_MAP_KEY_TYPE],
                    self::TYPE_MAP_KEY_TYPE_CHECK => $typeName
                ];
                $this->typeCheckFunction[$typeName] = $typeCheck[self::TYPE_MAP_KEY_TYPE_CHECK_TEMPLATE];
            }
        }
    }


    /**
     * 获取变量声明类型,例如int.string
     * @param $typeName
     * @return string
     */
    public function getTypeDeclareAsString($typeName)
    {
        $typeName = strtolower($typeName);
        if (isset($this->typeMap[$typeName])) {
            return $this->typeMap[$typeName][self::TYPE_MAP_KEY_TYPE];
        }
        return $typeName;
    }


    /**
     * 获取类型检测的关键字
     * @param $typeName
     * @return string
     */
    protected function getTypeCheckFunctionKey($typeName)
    {
        if (isset($this->typeMap[$typeName])) {
            if (isset($this->typeMap[$typeName][self::TYPE_MAP_KEY_TYPE_CHECK])) {
                return $this->typeMap[$typeName][self::TYPE_MAP_KEY_TYPE_CHECK];
            }
        }
        return $typeName;
    }

    /**
     * @param $typeName
     * @return mixed|null
     */
    public function getTypeCheckFunctionTemplate($typeName)
    {
        $typeCheckKey = $this->getTypeCheckFunctionKey($typeName);
        $typeCheckTemplate = isset($this->typeCheckFunction[$typeCheckKey]) ? $this->typeCheckFunction[$typeCheckKey] : null;
        if (is_null($typeCheckTemplate)) {
            return null;
        }
        return $typeCheckTemplate;
    }

    /**
     * 是否存在类型
     * @param $typeName
     * @return bool
     */
    public function hasTypeName($typeName)
    {
        $typeName = strtolower($typeName);

        return isset($this->typeMap[$typeName]);
    }
}