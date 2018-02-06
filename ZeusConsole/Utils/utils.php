<?php


namespace ZeusConsole\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use utilphp\util;
use ZeusConsole\Validator\Constraints\CSVForeignKey;
use ZeusConsole\Validator\Constraints\CSVUnique;


/**
 * Class utils
 * @package ZeusConsole\Utils
 */
class utils
{
    /**
     * 获取路径下得文件列表
     * @param $path
     * @param array $subfix_filters
     * @return array
     */
    static function getFiles($path, array $subfix_filters = [])
    {
        if (empty($path)) {
            return [];
        }
        $files = [];
        $current_dir = opendir($path); // opendir()返回一个目录句柄,失败返回false
        while (($file = readdir($current_dir)) !== false) { // readdir()返回打开目录句柄中的一个条目
            $sub_dir = $path . DIRECTORY_SEPARATOR . $file; // 构建子目录路径
            if ($file == '.' || $file == '..') {
                continue;
            } else if (is_dir($sub_dir)) { // 如果是目录,进行递归
                $files = array_merge($files, self::getfiles($sub_dir, $subfix_filters));

            } else { // 如果是文件,直接输出
                if (count($subfix_filters) == 0 || in_array(pathinfo($sub_dir, PATHINFO_EXTENSION), $subfix_filters)) {
                    $files[] = $sub_dir;
                }

            }
        }

        $files = array_unique($files);
        sort($files);
        return $files;
    }

    /**
     * 读取游戏的CSV配置
     * @param $filePath
     * @return array
     */
    private static function parseGameCsv($filePath)
    {
        $csv = new \parseCSV();
        $csv->encoding('gbk', 'utf-8');
        $csv->parse($filePath);
        return $csv->data;
    }

    /**
     * CSV 表格导出数据开始行数 比实际的excel中少一行,因为读取csv后会自动把第一行,转换成key
     */
    const CSV_DATA_ROW_NUM = 4;
    /**
     * CSV 表格导出校验字段行数 比实际的excel中少一行,因为读取csv后会自动把第一行,转换成key
     */
    const CSV_DATA_VALIDATION_ROW_NUM = 3;

    const exportCsvConfig_Mode_Export = 0;
    const exportCsvConfig_Mode_Check = 1;

    /**
     * 导出标题列
     * @param $csvFilePath
     * @param bool|true $exportServer
     * @param bool|true $exportClient
     * @return array
     */

    static function parseGameCsvDataTitle($csvFilePath, $exportServer = true, $exportClient = true)
    {
        $csvArray = self::parseGameCsv($csvFilePath);
        $arr = [];
        if (empty($csvArray)) {
            return $arr;
        }
        /**
         * 标题列
         */
        $column = $csvArray[0];
        /**
         * 需要导出的列
         */
        $exportColumns = [];

        //处理需要导出的列名
        foreach ($column as $key => $value) {
            $keyArr = explode('$', $key);
            //不是标准的导出体
            if (count($keyArr) != 2) {
                continue;
            }

            $keyOptions = $keyArr[1];
            $bExport = false;

            if ($exportServer && strpos($keyOptions, 's') !== false) {
                $bExport = true;
            } elseif ($exportClient && strpos($keyOptions, 'c') !== false) {
                $bExport = true;
            }

            if ($bExport) {
                $exportColumns[$key] = $keyArr[0];
            }

        }
        return $exportColumns;

    }

    /**
     * 导出csv有效数据
     * @param string $csvFilePath csv文件路径|csv文件内容
     * @param bool|true $exportServer
     * @param bool|true $exportClient
     * @param int $mode
     *
     * @return array
     */

    static function parseGameCsvData($csvFilePath, $exportServer = true, $exportClient = true,
                                     $mode = self::exportCsvConfig_Mode_Export)
    {
        $csvArray = self::parseGameCsv($csvFilePath);
        $arr = [];

        //导出错误,或者没有数据
        if (empty($csvArray)
            || count($csvArray) < self::CSV_DATA_ROW_NUM
        ) {
            return $arr;
        }

        /**
         * 需要导出的列
         */
        $exportColumns = self::parseGameCsvDataTitle($csvFilePath, $exportServer, $exportClient);


        //构建最终的数据
        $dataLength = count($csvArray);
        for ($i = self::CSV_DATA_ROW_NUM; $i < $dataLength; $i++) {
            $dataRow = $csvArray[$i];
            $firstValue = util::array_first($dataRow);

            if (util::starts_with($firstValue, "#")) {
                //#号开头的数据不导出
                continue;
            } elseif (empty($firstValue) && $firstValue !== '0') {
                //首行数据为空不导出
                continue;
            }
            $dataFilterRow = [];

            foreach ($exportColumns as $csvKey => $exportKey) {
                //如果是检测数据,保留原始数据格式
                if (!isset($dataRow[$csvKey])) {
                    continue;
                } elseif ($mode == self::exportCsvConfig_Mode_Export && $dataRow[$csvKey] === "") {
                    //如果是导出数据的操作,则不导出空数据,监测模式的空数据主要为了判断Notblank
                    continue;
                } else {

                    //赋值每一列数据
                    $dataString = $dataRow[$csvKey];
                    if (strpos($dataString, "'") !== false) {
                        $dataString = str_replace("'", "\\'", $dataString);
                    }
                    //此处转换主要是做检测从空字符串,转换为null
                    $dataFilterRow[$exportKey] = ($dataString === "" ? null : $dataString);

                }
            }
            if (!empty($dataFilterRow)) {
                if ($mode == self::exportCsvConfig_Mode_Check) {
                    $dataFilterRow['__line'] = $i + 2; //1是自动换行的时候将列头自动删除了,1是索引从1开始
                }
                $arr[] = $dataFilterRow;
            }
        }


        //检测数据
        if ($mode == self::exportCsvConfig_Mode_Check) {

            if (!isset($csvArray[self::CSV_DATA_VALIDATION_ROW_NUM])) {
                var_dump([$csvFilePath, $csvArray]);
            }
            $validationRow = $csvArray[self::CSV_DATA_VALIDATION_ROW_NUM];
            $exportValidationColumns = [];
            foreach ($exportColumns as $csvKey => $exportKey) {
                if (!empty($validationRow[$csvKey])) {
                    try {
                        $parser = new Parser();
                        $validationYAML = $parser->parse($validationRow[$csvKey]);
                        $validationArr = [];

                        if (is_array($validationYAML)) {
                            foreach ($validationYAML as $validateClass => $validateParams) {

                                //特殊处理外键检测
                                if ($validateClass == 'CSVForeignKey') {
                                    $className = CSVForeignKey::class;
                                    //目前局限于检测同级目录下的csv文件
                                    $foreignPathInfo = pathinfo($csvFilePath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $validateParams['csv'];
                                    static $checkForeignDatas = [];
                                    if (!isset($checkForeignDatas[$foreignPathInfo])) {
//                                        var_dump($foreignPathInfo);
                                        $checkForeignDatas[$foreignPathInfo] = self::parseGameCsvData($foreignPathInfo);
                                    }
                                    $foreignData = $checkForeignDatas[$foreignPathInfo];
                                    $validateParams['foreignCSVData'] = $foreignData;
                                } elseif ($validateClass == 'CSVUnique') {
                                    $className = CSVUnique::class;
                                    $validateParams = [
                                        'columnName' => $exportKey,
                                        'CSVDatas' => $arr,
                                    ];


                                } else {
                                    $className = "Symfony\\Component\\Validator\\Constraints\\" . $validateClass;
                                }
                                if (!class_exists($className)) {
                                    continue;
                                }


                                $validationArr[] = new $className($validateParams);

                            }
                        }
                        if (!empty($validationArr)) {
                            $exportValidationColumns[$exportKey] = $validationArr;
                        }
                    } catch (ParseException $e) {

                    }
                }
            }


            $validator = Validation::createValidator();
            $checkArrInfos = [];
            //检测数据
            $translator = new Translator('chs');
            $errMessageTemplate = '[行数:%lineinfo%][列名:%key% 值:%value%][错误信息:%message%]';
            foreach ($arr as $data) {
                foreach ($data as $exportKey => $exportValue) {
                    if (isset($exportValidationColumns[$exportKey])) {
                        $validationArr = $exportValidationColumns[$exportKey];

                        $validatorResults = $validator->validate($exportValue, $validationArr);
                        for ($i = 0; $i < $validatorResults->count(); $i++) {
                            $validatorResult = $validatorResults->get($i);

                            $checkArrInfos[] = $translator->trans($errMessageTemplate,
                                [
                                    '%lineinfo%' => $data['__line'],
                                    '%key%' => $exportKey,
                                    '%value%' => $exportValue,
                                    '%message%' => $validatorResult->getMessage()
                                ]);
                        }
                    }
                }
            }
            return $checkArrInfos;
        } else {
            return $arr;
        }

    }

    /**
     * 对操作系统进行判断
     * @return string
     */
    static function judgePath()
    {
        if (isset($_SERVER['PWD'])) {
            $path = $_SERVER['PWD'];
        } else {
            $path = getcwd();
        }
        return $path;
    }

    /**
     * 获取临时路径
     * @return string
     */
    static function getTempDirectoryPath()
    {
        $path = self::judgePath() . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        $fs = new Filesystem();
        $fs->mkdir($path);
        return $path;
    }

    /**
     * 获取缓存路径
     * @return string
     */
    static function getCacheDirectoryPath()
    {
        $path = self::judgePath() . DIRECTORY_SEPARATOR . 'Caches' . DIRECTORY_SEPARATOR;
        $fs = new Filesystem();
        $fs->mkdir($path);
        return $path;
    }

    /**
     * 创建SVN处理器
     * @param array $arguments
     * @param bool|true $appendDefaultUserNameOrPassword
     * @return \Symfony\Component\Process\Process
     */
    static function createSvnProcess(Array $arguments, $appendDefaultUserNameOrPassword = true)
    {
        return svn::createSvnProcess($arguments, $appendDefaultUserNameOrPassword);
    }

    /**
     * 获取指定svn路径的最新版本号
     * @param $svnPath
     * @return string
     */
    static function getSvnRevision($svnPath)
    {
        return svn::getSvnRevision($svnPath);

    }

    /**
     * @return Serializer
     */
    static function getSerializer()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer(),
            new GetSetMethodNormalizer(),
            new ArrayDenormalizer()];
        return new Serializer($normalizers, $encoders);
    }
}