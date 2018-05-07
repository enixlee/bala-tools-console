<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 20/03/2018
 * Time: 4:52 PM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\Parse;

/**
 * Class ParseTable
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\Parse
 */
class ParseTable extends Parse
{
    protected $database;
    protected $tableName;

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param mixed $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @var ParseColumn[]
     */
    protected $columns = [];

    /**
     * @return ParseColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }


    protected function parseImpl($parseData)
    {

        $nameParts = $parseData['no_quotes']['parts'];
        $this->tableName = array_last($nameParts);

        $this->parseColumns($parseData['create-def']['sub_tree']);
    }

    protected function parseColumns($columns)

    {
        foreach ($columns as $column) {
            $newColumn = ParseColumn::parse($column);

            if ($newColumn->isValid()) {
                $this->columns[] = $newColumn;
            }
        }
    }

    public function toArray()
    {
        $array['database'] = $this->database;
        $array['tableName'] = $this->tableName;

        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = $column->toArray();
        }
        $array['columns'] = $columns;


        return $array;
    }

    /**
     * @return null|ParseColumn
     */
    public function getPrimaryKeyColumn()
    {
        foreach ($this->columns as $column) {
            if ($column->isPrimary()) {
                return $column;
            }
        }
        return null;
    }


}