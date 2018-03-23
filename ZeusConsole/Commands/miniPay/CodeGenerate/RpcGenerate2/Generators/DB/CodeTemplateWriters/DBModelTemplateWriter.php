<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 21/03/2018
 * Time: 11:02 AM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\CodeTemplateWriters;


class DBModelTemplateWriter extends WriterBase
{

    protected $ignoreColumns = [
        'created_at',
        'updated_at',
        'deleted_at',
        'luck_version',
    ];
    protected $casts = [
        "int" => 'int',
        "integer" => 'integer',
        "bigint" => 'bigint',
        "real" => 'real',
        "float" => 'float',
        "double" => 'double',
        "text" => 'text',
        "string" => 'string',
        "bool" => 'bool',
        "boolean" => 'boolean',
        "object" => 'object',
        "array" => 'array',
        "json" => 'json',
        "collection" => 'collection',
        "date" => 'date',
        "datetime" => 'datetime',
        "timestamp" => 'timestamp',
        "tinyint" => 'tinyint',
        "smallint" => 'smallint',
        "decimal" => 'decimal'
    ];

    function writeClassComment()
    {
        $format = <<<EOF
/**
 * Class %classname%
 * @package %namespace%
%property%
 *
 */\n
EOF;

        $formatProperty = <<<EOF
 * @property mixed %keyName%    %dateType%(%length%)    %nullAble% COMMENT %comment% \n
EOF;


        $columns = $this->generateClass->getTable()->getColumns();
        $castString = "";
        foreach ($columns as $column) {

            if (in_array($column->getName(), $this->ignoreColumns)) {
                continue;
            }
            $castString .= translator()->trans($formatProperty,
                [
                    "%keyName%" => $column->getName(),
                    "%dateType%" => $column->getDateType(),
                    "%nullAble%" => $column->isNullAble() ? "NULL" : "NOT NULL",
                    "%comment%" => $column->getComment(),
                    "%length%" => $column->getLength()
                ]);
        }


        $format = translator()->trans($format,
            [
                "%classname%" => $this->generateClass->getClassName(),
                "%namespace%" => $this->generateClass->getNameSpace(),
                "%property%" => rtrim($castString)
            ]);

//        dumpLine($format);

        return $format;

    }

    function writeUseDocument()
    {
        $format = <<<EOF
use Pluto\DataBases\Model;
use Pluto\Interfaces\DataBases\ModelDataType;
EOF;

        return $format;
    }


    function writeCastsDocument()
    {
        $castFormat = <<<EOF
    //类型转换
    protected \$casts = [
%casts%
    ];
EOF;

        $columns = $this->generateClass->getTable()->getColumns();

        $castString = "";
        foreach ($columns as $column) {

            if (in_array($column->getName(), $this->ignoreColumns)) {
                continue;
            }
            if (isset($this->casts[strtolower($column->getDateType())])) {
                $castString .= $this->format_tab_2 . "'" . $column->getName() . "' => ModelDataType::" . $this->casts[strtolower($column->getDateType())] . ",\n";
            }
        }

        $castFormat = translator()->trans($castFormat,
            [
                "%casts%" => $castString
            ]);


        return $castFormat;

    }
}