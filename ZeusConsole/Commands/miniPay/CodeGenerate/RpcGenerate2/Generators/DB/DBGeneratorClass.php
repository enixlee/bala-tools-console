<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 21/03/2018
 * Time: 11:05 AM
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Yaml\Yaml;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\Parse\ParseTable;
use ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\RpcGenerate2;

/**
 * Class DBGeneratorClass
 * @package ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB
 */
class DBGeneratorClass
{
    /**
     * @var $mainClass RpcGenerate2
     */
    protected $mainClass;

    /**
     * @return RpcGenerate2
     */
    public function getMainClass()
    {
        return $this->mainClass;
    }

    /**
     * @param RpcGenerate2 $mainClass
     */
    public function setMainClass(RpcGenerate2 $mainClass)
    {
        $this->mainClass = $mainClass;
    }

    /**
     * @var $table ParseTable
     */
    protected $table;

    /**
     * @return ParseTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param ParseTable $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }


    protected $exportPath;

    /**
     * @return mixed
     */
    public function getExportPath()
    {
        return $this->exportPath;
    }

    /**
     * @param mixed $exportPath
     */
    public function setExportPath($exportPath)
    {
        $this->exportPath = $exportPath;
    }


    /**
     * 导出命名空间
     * @return string
     */
    public function getNameSpace()
    {
        return $this->mainClass->getExportNameSpace() . "\\" . $this->table->getDatabase();
    }


    public function dumpTable()
    {
        $this->dumpTableYaml();
        $this->dumpTablePhp();
    }


    public function dumpTableYaml()
    {
        $tableArray['yamlType'] = 'database.table';
        $tableArray = array_merge($tableArray, $this->getTable()->toArray());
        $yaml = Yaml::dump($tableArray);

        $exportPath = $this->exportPath . '/yaml/databases/' . $this->getTable()->getDatabase() . '/' . $this->getTable()->getTableName() . '.yaml';

        $fs = new Filesystem();
        $fs->dumpFile($exportPath, $yaml);
    }

    public function dumpTablePhp()
    {
        $exportPath = $this->exportPath . '/bala/DBTemplate/' . $this->getTable()->getDatabase() . '/' . $this->getTable()->getTableName() . '.php';

//        dumpLine($exportPath);
//        dumpLine($this->getNameSpace());

        $loader = new FilesystemLoader([__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%"]);
        $template = new PhpEngine(new TemplateNameParser(), $loader);


        $renderTemplate = $template->render("DBModelTemplate.php", [
            'generateClass' => $this,
        ]);


        $fs = new Filesystem();
        $fs->dumpFile($exportPath, $renderTemplate);


    }


    public function getClassName()
    {
        return $this->table->getTableName();
    }

    public function getPrimaryKey()
    {
        return $this->table->getPrimaryKeyColumn()->getName();
    }


}