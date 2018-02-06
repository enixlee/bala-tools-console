<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/19
 * Time: 下午2:57
 */

namespace ZeusConsole\Commands\MakeClass;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

class MakeDataCell extends CommandBase
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('makeClass:DataCell')->setDescription('创建服务端数据模板类');

        $this->setAliases(["webGameCodeTemplate:MakeDataBase"]);
        $this->addOption('templatePath', null, InputOption::VALUE_OPTIONAL, "模板源路径",
            $this->getDBSTemplatePath());
        $this->addOption('exportPath', null, InputOption::VALUE_OPTIONAL, "类导出路径",
            $this->getExportPath());


        $this->addOption('superNameSpace', null, InputOption::VALUE_OPTIONAL, "导出类的命名空间",
            "dbs\\templates");

        $this->addOption('parentClassPlayerDB', null, InputOption::VALUE_OPTIONAL, "用户类导出的父类",
            "dbs\\dbs_baseplayer");
        $this->addOption('parentClassDataCell', null, InputOption::VALUE_OPTIONAL, "基本数据类导出的父类",
            "dbs\\dbs_basedatacell");
        $this->addOption('parentClassGlobalDB', null, InputOption::VALUE_OPTIONAL, "全局DB导出父类",
            "dbs\\dbs_base");


    }

    /**
     * 父类
     * @var array
     */
    private $parentClass = [];
    /**
     * 类型转换信息
     * @var array
     */
    private $typeInfos = [
        "int" => [
            "convertFunction" => "intval",
            "defaultValue" => "0",
        ],
        "array" => [
            "convertFunction" => "",
            "defaultValue" => "[]",
        ],
        "string" => [
            "convertFunction" => "strval",
            "defaultValue" => "\"\"",
        ],
        "bool" => [
            "convertFunction" => "boolval",
            "defaultValue" => "false",
        ],
        "float" => [
            "convertFunction" => "floatval",
            "defaultValue" => "0.0",
        ],
        "double" => [
            "convertFunction" => "doubleval",
            "defaultValue" => "0.0",
        ],
        "mixed" => [
            "convertFunction" => "",
            "defaultValue" => "\"\"",
        ]

    ];


    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->initailizeParentClass($input);

        $templatePath = $input->getOption('templatePath');
        $exportPath = $input->getOption('exportPath');
        $superNameSpace = $input->getOption('superNameSpace');

        $output->writeln("<info>数据模板路径:$templatePath</info>");
        $output->writeln("<info>数据导出路径:$exportPath</info>");

        $finder = new Finder();
        $iterator = $finder->files()
            ->name('*.yaml')
            ->depth('<5')
            ->in($templatePath);

        //清理目标路径
        $fs = new Filesystem();
        $fs->remove($exportPath);

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }
//            var_dump([
//                $file->getRealpath(),
//                $file->getRelativePath(),
//                $file->getRelativePathname(),
//            ]);

            $RelativePath = $file->getRelativePath();
//            var_dump($RelativePath);

            $yamlString = $file->getContents();
            $dataTemplate = Yaml::parse($yamlString);
//            $dataTemplate = [
//                "className" => "role",
//                "datas" => [
//                    [
//                        "name" => "roleName",
//                        "type" => "string",
//                        "defaultValue" => '"a"',
//                        "comment" => "用户名"
//                    ],
//                    [
//                        "name" => "UserId",
//                        "type" => "int",
//                        "defaultValue" => 2,
//                        "comment" => "用户ID"
//                    ]
//                ]
//            ];


            $dataCells = "";
            $dataCellDefaultValues = "";
            $CellTemplateString = $this->getCellTypeTemplate();
            $CellDefaultTemplateString = $this->getCellDefaultValueTemplate();


            //填充dataType
            $fillData = [];
            $fillData["{{ valueconvert }}"] = $this->getConvertString("string");
            $fillData["{{ defaultValue }}"] = '"' . (empty($RelativePath) ? "" : str_replace(DIRECTORY_SEPARATOR, ".", $RelativePath) . ".") . $dataTemplate["className"] . '"';
            $fillData["{{ accessor }}"] = "private";
            $fillData["{{ type }}"] = "string";
            $fillData["{{ comment }}"] = "数据类型";
            $fillData["{{ name }}"] = "dataTemplateType";

            $dataCells .= translator()->trans($CellTemplateString, $fillData);
            $dataCellDefaultValues .= translator()->trans($CellDefaultTemplateString, $fillData);

            $CellTemplateString = $this->getCellTemplate();

            if (is_array($dataTemplate["datas"]) && !empty($dataTemplate["datas"])) {
                foreach ($dataTemplate["datas"] as $dataCell) {

                    $fillData = [];
                    foreach ($dataCell as $key => $value) {
                        $fillData["{{ $key }}"] = $value;
                    }
                    //设置默认值
                    $fillData["{{ valueconvert }}"] = $this->getConvertString($dataCell["type"]);
                    $fillData["{{ defaultValue }}"] = isset($dataCell["defaultValue"]) ? $this->convertValueByType($dataCell["type"], $dataCell["defaultValue"]) :
                        $this->getDefaultValueByType($dataCell["type"]);

                    //访问规则
                    $access = isset($dataCell["access"]) ? $dataCell["access"] : 0;
                    switch ($access) {
                        case 0:
                            //可读写
                            $fillData["{{ get_accessor }}"] = "public";
                            $fillData["{{ set_accessor }}"] = "public";
                            break;
                        case 1:
                            //只读
                            $fillData["{{ get_accessor }}"] = "public";
                            $fillData["{{ set_accessor }}"] = "protected";
                            break;
                        case 2:
                            //只写
                            $fillData["{{ get_accessor }}"] = "protected";
                            $fillData["{{ set_accessor }}"] = "public";
                            break;
                        case 3:
                            $fillData["{{ get_accessor }}"] = "protected";
                            $fillData["{{ set_accessor }}"] = "protected";
                            //内部访问
                            break;
                        default:
                            $fillData["{{ get_accessor }}"] = "public";
                            $fillData["{{ set_accessor }}"] = "public";
                            break;
                    }

                    $fillData["{{ reset_accessor }}"] = "public";

                    $dataCells .= translator()->trans($CellTemplateString, $fillData);
                    $dataCellDefaultValues .= translator()->trans($CellDefaultTemplateString, $fillData);
                }
            }


            $className = empty($RelativePath) ? "dbs_templates_" . $dataTemplate["className"] :
                "dbs_templates_" . str_replace(DIRECTORY_SEPARATOR, "_", $RelativePath) . "_" . $dataTemplate["className"];
            //获取输出类模板
            $classTemplateString = $this->getClassTemplate($dataTemplate["DBType"]);
            //命名空间
            $nameSpace = empty($RelativePath) ? $superNameSpace : $superNameSpace . "\\"
                . str_replace(DIRECTORY_SEPARATOR, "\\", $RelativePath);


            //父类名称
            $parentClassName = $this->getParentClassByType($dataTemplate["DBType"]);
            if (isset($dataTemplate["parent"])) {
                //父类的命名空间.处理多级命名空间
                $parentNameSpaceArr = explode(".", $dataTemplate["parent"]);
                array_pop($parentNameSpaceArr);
                $parentNameSpace = join("\\", $parentNameSpaceArr);
                $parentClassName = $superNameSpace;
                //不是最外层的父类
                if ($parentClassName !== $dataTemplate["parent"]) {
                    $parentClassName .= "\\$parentNameSpace\\";
                }
                $parentClassName .= "dbs_templates_" . str_replace(".", "_", $dataTemplate["parent"]);
            }

            $classString = translator()->trans($classTemplateString,
                [
                    "{{ namespace }}" => $nameSpace,
                    "{{ className }}" => $className,
                    "{{ tableName }}" => isset($dataTemplate['TableName']) ? $dataTemplate['TableName'] : '',
                    "{{ dataCells }}" => $dataCells,
                    "{{ parent }}" => $parentClassName,
                    "{{ defaultValues }}" => $dataCellDefaultValues,
                ]);

            //导出文件名
            $exportFileName = "$className.php";

            if (empty($RelativePath)) {
                $fileFullPath = $exportPath . DIRECTORY_SEPARATOR .
                    $exportFileName;
            } else {
                $fileFullPath = $exportPath . DIRECTORY_SEPARATOR . $RelativePath . DIRECTORY_SEPARATOR .
                    $exportFileName;
            }
            $fs->dumpFile($fileFullPath, $classString);


            $output->writeln([
                "<info>导出文件: $fileFullPath</info>",
            ]);
        }

        $output->writeln("<info>导出完毕,共导出文件 " . count($iterator) . "个</info>");
        return 0;
    }

    /**
     * 获取文件数据模板路径
     */
    private function getDBSTemplatePath()
    {
        $path = getConfig('MakeClass.DBSTemplatePath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'DBSTemplatePath';
        }
        return $path;
    }

    /**
     * 获取导出路径
     * @return array|null|string
     */
    private function getExportPath()
    {
        $path = getConfig('MakeClass.DBSExportPath');
        if (empty($path)) {
            $path = utils::getTempDirectoryPath() . 'exports';
        }
        return $path;
    }

    /**
     * 获取代码模板路径
     * @param $filePath
     * @return string
     */
    private function getCodeTemplatePath($filePath)
    {
        $templateFilePath = __DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates" . DIRECTORY_SEPARATOR . $filePath;
        return $templateFilePath;

    }

    /**
     * 获取默认值模板
     * @return string
     */
    private function getCellDefaultValueTemplate()
    {
        $templateFilePath = $this->getCodeTemplatePath("DataCellDefaultValues.rtf");
        $templateString = file_get_contents($templateFilePath);
        return $templateString;
    }

    private function getCellTypeTemplate()
    {
        $templateFilePath = $this->getCodeTemplatePath("DataCellType.rtf");
        $templateString = file_get_contents($templateFilePath);
        return $templateString;
    }

    private function getCellTemplate()
    {
        $templateFilePath = $this->getCodeTemplatePath("DataCell.rtf");
        $templateString = file_get_contents($templateFilePath);
        return $templateString;
    }


    /**
     * @return string
     */
    private function getClassTemplate($type)
    {

//        $templateFilePath = __DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates" . DIRECTORY_SEPARATOR;
        switch ($type) {
            case "playerDB":
                $templateFilePath = "DataCellClassPlayerDB.rtf";
                break;
            case "dataCell":
                $templateFilePath = "DataCellClassDataCell.rtf";
                break;
            case "globalDB":
                $templateFilePath = "DataCellClassGlobalDB.rtf";
                break;
            default :
                $templateFilePath = "DataCellClassDataCell.rtf";
                break;

        }

        $templateString = $this->getCodeTemplatePath($templateFilePath);

        return file_get_contents($templateString);
    }


    /**
     * 获取转换默认值
     * @param $type
     * @return string
     */
    private function getConvertString($type)
    {
        $type = strtolower($type);
        $convertFunction = $this->typeInfos[$type]["convertFunction"];
        if (empty($convertFunction)) {
            $convertString = '$value';
        } else {
            $convertString = $convertFunction . '($value)';
        }
        return $convertString;
    }

    /**
     * 获取默认值
     * @param $type
     * @return int|string
     */
    private function getDefaultValueByType($type)
    {
        $type = strtolower($type);
        $defaultValue = $this->typeInfos[$type]["defaultValue"];
        return $defaultValue;

    }

    /**
     * 转换值
     * @param $type
     * @param $value
     * @return string
     */
    private function convertValueByType($type, $value)
    {
        $type = strval($type);
        $defaultValue = $value;
        switch ($type) {
            case "string":
                $defaultValue = "\"$value\"";
                break;
        }
        return $defaultValue;
    }

    private function initailizeParentClass(InputInterface $input)
    {
        $this->parentClass['playerDB'] = $input->getOption('parentClassPlayerDB');
        $this->parentClass['dataCell'] = $input->getOption('parentClassDataCell');
        $this->parentClass['globalDB'] = $input->getOption('parentClassGlobalDB');
    }

    /**
     * 获取基类
     * @param $type
     * @return string
     */
    private function getParentClassByType($type)
    {
        $type = strval($type);
        $defaultValue = '';
        if (isset($this->parentClass[$type])) {
            return $this->parentClass[$type];
        }
        return $defaultValue;
    }


}