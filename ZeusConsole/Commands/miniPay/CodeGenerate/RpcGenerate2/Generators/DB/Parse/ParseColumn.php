<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 20/03/2018
 * Time: 4:51 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\Parse;


class ParseColumn extends Parse
{

    protected $name;
    protected $dateType;
    protected $unsigned = false;
    protected $zerofill = false;
    protected $primary = false;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * @return bool
     */
    public function isZerofill(): bool
    {
        return $this->zerofill;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return intval($this->length);
    }

    /**
     * @return bool
     */
    public function isNullAble(): bool
    {
        return $this->nullAble;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }
    protected $default = null;
    protected $comment = "";
    protected $length = 0;
    protected $nullAble = false;
    protected $unique = false;

    protected function parseImpl($parseData)
    {

        //parseKey
        $columnData = $parseData['sub_tree'];
        foreach ($columnData as $columnDatum) {
            $this->parseBlock($columnDatum);
        }
    }


    protected function parseBlock($block)
    {
        $expr_type = $block['expr_type'];
        switch ($expr_type) {
            case "colref":
                $this->parseBlock_colref($block);
                break;
            case "column-type":
                $this->parseBlock_column_type($block);
                break;

            case "data-type":
                $this->parseBlock_data_type($block);
                break;
        }

    }

    /**
     * 解析定义
     * @param $block
     */
    protected function parseBlock_colref($block)
    {
        $nameParts = $block['no_quotes']['parts'];
        $this->name = array_last($nameParts);
    }

    protected function parseBlock_column_type($block)
    {
//        dumpLine($block);
        $this->unique = $block['unique'];
        $this->nullAble = $block['nullable'];
        $this->primary = $block['primary'];
        $this->default = $block['default'] ?? null;
        $this->comment = $block['comment'];


        $subtree = $block['sub_tree'];
        foreach ($subtree as $item) {
            $this->parseBlock($item);
        }

    }

    protected function parseBlock_data_type($block)
    {
//        dumpLine($block);
        $this->dateType = $block['base_expr'];
        $this->length = $block['length'] ?? null;
        $this->unsigned = $block['unsigned'] ?? false;
        $this->zerofill = $block['zerofill'] ?? false;
    }

    public function toArray()
    {

        $array['name'] = $this->name;
        $array['dataType'] = $this->dateType;
        $array['unsigned'] = $this->unsigned;
        $array['zerofill'] = $this->zerofill;
        $array['primary'] = $this->primary;
        $array['default'] = $this->default;
        $array['comment'] = $this->comment;
        $array['length'] = $this->length;
        $array['nullAble'] = $this->nullAble;
        $array['unique'] = $this->unique;
        return $array;

    }


}