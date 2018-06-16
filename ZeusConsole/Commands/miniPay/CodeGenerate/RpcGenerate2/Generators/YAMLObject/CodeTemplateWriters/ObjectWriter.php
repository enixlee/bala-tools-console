<?php
/**
 * Created by PhpStorm.
 * User: peng.zhi
 * Date: 2018/4/20
 * Time: 8:00 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\CodeTemplateWriters;


use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\YamlObjectGeneratorClass;

class ObjectWriter extends WriterBase
{
    /**
     * @var YamlObjectGeneratorClass
     */
    protected $mainClass;

    /**
     * ObjectWriter constructor.
     * @param YamlObjectGeneratorClass $mainClass
     */
    public function __construct($mainClass)
    {
        $this->mainClass = $mainClass;
    }

    public function writeClassComment()
    {
        $format = <<<EOF
/**
 *
 * %description%
 * %deprecated%
 * @package %namespace%
 */

EOF;
        $setData = [
            "%description%" => $this->mainClass->getDescription(),
            "%deprecated%" => $this->mainClass->isDeprecated() ? "@deprecated" : "",
            "%namespace%" => $this->mainClass->getNameSpace()
        ];

        return translator()->trans($format, $setData);
    }

    public function writeClassName()
    {
        $format = <<<EOF
class %className% extends YAMLArrayObject\n
EOF;
        if (!is_null($this->mainClass->getExtends())) {
            $format = <<<EOF
class %className% extends %parent%\n
EOF;
        }


        $setData = [
            "%className%" => $this->mainClass->getClassName(),
            "%parent%" => $this->mainClass->getExtends(),
        ];

        return translator()->trans($format, $setData);
    }


    public function writeObjectProperty()
    {

        $fillableFormatNoExtends = <<<EOF
    protected \$fillable = [
%fillable%        
    ];
    
EOF;
        $fillableFormatExtends = <<<EOF
    public function getFillable()
    {
        return array_merge(parent::getFillable(),
            [
%fillable%
            ]);
    }

EOF;


        $format = <<<EOF
%fillable%

    /**
     * 对象名称
     * @return string
     */
    public function getObjectName()
    {
        return "%className%";
    }

    /**
     * 版本号
     * @return int
     */
    public function getObjectVersion()
    {
        return %version%;
    }

EOF;
        $fillParameters = "";
        $parameters = $this->mainClass->getParameters();
        foreach ($parameters as $parameter) {
            $fillParameters .= $this->format_tab_2 . $this->format_tab_2 . "'{$parameter->getName()}'" . ",\n";
        }

        $fillDataSetData = [
            '%fillable%' => $fillParameters,
        ];
        if (is_null($this->mainClass->getExtends())) {
            $fillData = translator()->trans($fillableFormatNoExtends, $fillDataSetData);
        } else {
            $fillData = translator()->trans($fillableFormatExtends, $fillDataSetData);
        }


        $setData = [
            '%fillable%' => $fillData,
            "%className%" => $this->mainClass->getClassName(),
            "%version%" => $this->mainClass->getVersion(),
        ];

        return translator()->trans($format, $setData);

    }


}