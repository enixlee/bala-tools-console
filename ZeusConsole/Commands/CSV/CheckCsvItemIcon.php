<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/19
 * Time: 下午4:24
 */

namespace ZeusConsole\Commands\CSV;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use utilphp\util;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

/**
 * Class CheckCsvItemIcon
 * @package ZeusConsole\Commands\CSV
 */
class CheckCsvItemIcon extends CommandBase
{
    private $errorNum = 0;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('csv:checkitemicon')->setDescription('检查美术资源的图片错误');
        $this->setAliases(['csv:checkImages']);

        $this->addOption('svn-csv-path', null, InputOption::VALUE_REQUIRED, 'svn路径004000表',
            'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格/004000.道具配置$item_setting$cs.csv');
        $this->addOption('svn-img-path', null, InputOption::VALUE_REQUIRED, 'icon查找路径',
            'svn://192.168.1.2/cooking_docs/trunk/3-美术目录/3-程序用资源');


    }

    private $svnAuth = [
        '--username',
        'zhipeng',
        '--password',
        'zhipeng001',
        '--no-auth-cache'
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

        $imgFiles = $this->SVNLs($input->getOption('svn-img-path'));
        $csvArrs = $this->SVNCatCsv($input->getOption('svn-csv-path'));

        $pngKeys = [
            'icon',
        ];
        foreach ($csvArrs as $csvArr) {
            foreach ($csvArr as $csvKey => $csvValue) {
                if (in_array($csvKey, $pngKeys)) {
                    $findFileName = DIRECTORY_SEPARATOR . $csvValue . '.png';
                    $findResult = array_filter($imgFiles, function ($arr) use ($findFileName) {
                        if (util::ends_with($arr, $findFileName)) {
                            return true;
                        }
                        return false;

                    });
                    if (empty($findResult)) {
                        $this->outputErrorMessage('图标不存在itemid:' . $csvArr['id'] . ':' . $csvKey . ',icon:' . $csvValue, $output);
                    }
                } elseif ($csvKey === 'buildingimg') {
                    for ($i = 0; $i < 2; $i++) {
                        $findFileName = DIRECTORY_SEPARATOR . $csvValue . '_' . $i . '.png';
                        $findResult = array_filter($imgFiles, function ($arr) use ($findFileName) {
                            if (util::ends_with($arr, $findFileName)) {
                                return true;
                            }
                            return false;

                        });
                        if (empty($findResult)) {
                            $this->outputErrorMessage('图标不存在itemid:' . $csvArr['id'] . ':' . $csvKey . ',icon:' . $findFileName, $output);
                        }
                    }
                }
            }
        }

        //额外检测
        $this->checkNpcResource($imgFiles, $output);
        $this->checkDishesImage($imgFiles, $output);

        /*$this->checkImageNormal('010000.任务系统基础配置$mission_setting$cs.csv',
            [
                'completeconditionvalue1icon',
                'completeconditionvalue2icon',
                'completeconditionvalue3icon',
                'icon'
            ],
            $imgfiles, $output);*/


        $this->checkImageNormal('010006.任务系统成就配置$mission_achievement_setting$cs.csv',
            [
                'icon'
            ],
            $imgFiles, $output);
        $this->checkImageNormal('011001.场景功能建筑配置$scene_functionbuilding$cs.csv',
            [
                'buildingicon',
            ],
            $imgFiles, $output);

        $this->checkImageNormal('011002.场景宝箱配置$scene_box_setting$cs.csv', ['image'], $imgFiles, $output);
        $this->checkImageNormal('013009.客户端PVE推图地图显示配置$client_pve_map_display_setting$c.csv',
            [
                'mapicon_0',
                'mapicon_1'
            ],
            $imgFiles, $output);
        $this->checkImageNormal('019000.充值配置$recharge_setting$cs.csv',
            [
                'icon',
            ],
            $imgFiles, $output);

        $this->checkImageNormal('025001.PVE推图配置$pve_map_setting$cs.csv',
            [
                'stageicon',
            ],
            $imgFiles, $output);

        $this->checkImageEmptyName($imgFiles, $output);

        $this->checkNpcKeelCartoon('svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YYResource/', $output);

        $this->checkFileNamesRules($imgFiles, $output);

        $this->checkEmptybuildingimg($csvArrs, $imgFiles, $output);

        $this->checkImageNamesPrefix($imgFiles, $output);

        $this->checkFilenamesContainsUnderline($imgFiles, $output);

        $this->checkAnimationFileName($imgFiles, $output);

        $output->writeln('<info>检查完成</info>');


        return $this->errorNum;

    }

    /**
     * @param $message
     * @param OutputInterface $output
     */
    private function outputErrorMessage($message, OutputInterface $output)
    {
        $message = '<error>' . $this->errorNum . '.' . $message . '</error>';
        $this->errorNum++;

        $output->writeln($message);

    }

    /**
     * 显示svn中的文件内容
     * @param $svnUrl
     * @return array
     */
    private function SVNCatCsv($svnUrl)
    {

        $process = utils::createSvnProcess([
            'cat',
            $svnUrl
        ]);
        $process->run();
        $CSVFileContent = $process->getOutput();

        $csvArrs = utils::parseGameCsvData($CSVFileContent, false, true);
        return $csvArrs;

    }

    /**
     * 获取SVN路径中得文件列表
     * @param $svnUrl
     * @return array
     */
    private function SVNLs($svnUrl)
    {


        $process = utils::createSvnProcess([
            'ls',
            '-R',
            $svnUrl
        ]);
        $process->run();

        return explode("\n", $process->getOutput());
    }

    /**
     * 检测npc资源
     * @param array $files
     * @param OutputInterface $output
     */
    private function checkNpcResource(array $files, OutputInterface $output)
    {


        $svnFileName = '002001.NPC美术资源配置$npc_res_config$c.csv';
        $svnUrl = 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格/' . $svnFileName;
        $csvArrs = $this->SVNCatCsv($svnUrl);

        $flaKeys = ['head0_1', 'head0_2', 'body0', 'leg0', 'head1_1', 'head1_2', 'body1', 'leg1'];
        foreach ($csvArrs as $csvArr) {
            foreach ($csvArr as $csvKey => $csvValue) {
                if (in_array($csvKey, $flaKeys)) {
                    $findFileName = DIRECTORY_SEPARATOR . $csvValue . '.fla';
                    $findResult = array_filter($files, function ($arr) use ($findFileName) {
                        if (util::ends_with($arr, $findFileName)) {
                            return true;
                        }
                        return false;

                    });
                    if (empty($findResult)) {
                        $this->outputErrorMessage($svnFileName . ':' . $csvArr['resourceid'] . ',fla:' . $findFileName, $output);
                    }
                } elseif ($csvKey === 'texture') {
                    $findFileName = DIRECTORY_SEPARATOR . $csvValue . '.png';
                    $findResult = array_filter($files, function ($arr) use ($findFileName) {
                        if (util::ends_with($arr, $findFileName)) {
                            return true;
                        }
                        return false;

                    });
                    if (empty($findResult)) {
                        $this->outputErrorMessage($svnFileName . ':' . $csvArr['resourceid'] . ',png:' . $findFileName, $output);
                    }
                }
            }
        }

    }

    /**
     * @param array $files
     * @param OutputInterface $output
     */
    private function  checkDishesImage(array $files, OutputInterface $output)
    {
        $svnFileName = '004004.道具菜品配置$item_dishes_setting$cs.csv';
        $svnUrl = 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格/' . $svnFileName;
        $csvArrs = $this->SVNCatCsv($svnUrl);


        foreach ($csvArrs as $csvArr) {
            foreach ($csvArr as $csvKey => $csvValue) {
                if ($csvKey === 'icon_cookcomplete') {
                    for ($i = 0; $i < 3; $i++) {
                        $findFileName = DIRECTORY_SEPARATOR . $csvValue . '_' . $i . '.png';
                        $findResult = array_filter($files, function ($arr) use ($findFileName) {
                            if (util::ends_with($arr, $findFileName)) {
                                return true;
                            }
                            return false;

                        });
                        if (empty($findResult)) {
                            $this->outputErrorMessage($svnFileName . ':' . $csvArr['id'] . ',file:' . $findFileName, $output);
                        }
                    }
                } elseif ($csvKey === 'cookingicon') {
                    $findFileName = DIRECTORY_SEPARATOR . $csvValue . '_1' . '.png';
                    $findResult = array_filter($files, function ($arr) use ($findFileName) {
                        if (util::ends_with($arr, $findFileName)) {
                            return true;
                        }
                        return false;

                    });
                    if (empty($findResult)) {
                        $this->outputErrorMessage($svnFileName . ':' . $csvArr['id'] . ',file:' . $findFileName, $output);
                    }
                }
            }
        }

    }


    /**
     * 通用图片检测
     * @param $svnFileName
     * @param array $checkKeys
     * @param $files
     * @param OutputInterface $output
     * @param string $outputKey
     * @param string $searchSubfix
     */
    private function checkImageNormal($svnFileName, array $checkKeys, $files, OutputInterface $output, $outputKey = 'id',
                                      $searchSubfix = '.png')
    {
//        $svnFileName = '011001.场景功能建筑配置$scene_functionbuilding$cs.csv';
        $svnUrl = 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格/' . $svnFileName;
        $csvArrs = $this->SVNCatCsv($svnUrl);


        $searchKeys = $checkKeys;
        foreach ($csvArrs as $csvArr) {
            foreach ($csvArr as $csvKey => $csvValue) {
                if (in_array($csvKey, $searchKeys)) {
                    $findFileName = DIRECTORY_SEPARATOR . $csvValue . $searchSubfix;
                    $findResult = array_filter($files, function ($arr) use ($findFileName) {
                        if (util::ends_with($arr, $findFileName)) {
                            return true;
                        }

                        return false;

                    });
                    if (empty($findResult)) {
                        $this->outputErrorMessage($svnFileName . ':id:' . $csvArr[$outputKey] . ',文件不存在.file:' . $findFileName, $output);
                    }
                }
            }
        }
    }

    /**
     * 检测图片的名字是否有空格
     * @param  array $files
     * @param OutputInterface $output
     * @param string $searchSubfix
     */
    private function checkImageEmptyName(array $files, OutputInterface $output, $searchSubfix = '.png')
    {
        $findResult = array_filter($files, function ($file) use ($searchSubfix) {
            if (util::ends_with($file, $searchSubfix)) {
                $fileName = basename($file);    //获取当前文件名
                return strpos($fileName, " ");
            }
        });
        if (!empty($findResult)) {
            foreach ($findResult as $fileName) {
                $this->outputErrorMessage('名称不能有空格,fileName:' . $fileName, $output);
            }
        }
    }

    /**
     * Npc龙骨动画检测
     * @param string $svnUrlResource
     * @param OutputInterface $output
     */
    private function checkNpcKeelCartoon($svnUrlResource, OutputInterface $output)
    {

        $svnFileName = '002001.NPC美术资源配置$npc_res_config$c.csv';
        $svnUrl = 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格/' . $svnFileName;
        $csvArrs = $this->SVNCatCsv($svnUrl);
        $flaKeys = ['head0_1', 'head0_2', 'body0', 'leg0'];

        $fileNames = $this->SVNLs($svnUrlResource);    //显示SVN文件中的内容

        $dirs = array();
        foreach ($fileNames as $fileName) {
            if (empty($fileName)) {
                continue;
            }
            if (util::ends_with($fileName, "/")) {  //查找文件夹
                $dirs[trim($fileName, "/")] = 1;
            }
        }

        foreach ($csvArrs as $csvArr) {
            foreach ($csvArr as $csvKey => $csvValue) {
                if (!in_array($csvKey, $flaKeys)) {
                    continue;
                }
                if (!isset($dirs[$csvValue])) {
                    $this->outputErrorMessage('检测路径：” svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YYResource/下，不存在以表002001.NPC美术资源配置$npc_res_config$c.csv：
 “字段：head0_1，head0_2，body0，leg0“的值命名的目录:' . $csvValue, $output);
                } else {
                    $dirsFiles = [];
                    $dirsFiles[] = $csvValue . "/skeleton.xml";
                    $dirsFiles[] = $csvValue . "/texture.pvr.ccz";
                    $dirsFiles[] = $csvValue . "/texture.xml";

                    $csvValueReplace = str_replace('_0_', '_1_', $csvValue);

                    $dirsFiles[] = $csvValueReplace . "/skeleton.xml";
                    $dirsFiles[] = $csvValueReplace . "/texture.pvr.ccz";
                    $dirsFiles[] = $csvValueReplace . "/texture.xml";

                    foreach ($dirsFiles as $dirsFile) {
                        if (!in_array($dirsFile, $fileNames)) {
                            $this->outputErrorMessage('检测路径：” svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YYResource/下，该文件夹下不存在这个文件:' . $dirsFile, $output);
                        }
                    }

                }

            }
        }

    }

    /**
     * 检测目录和文件名中是否存在中文，空格，'-'
     * @param array $files
     * @param OutputInterface $output
     */

    private function checkFileNamesRules(array $files, OutputInterface $output)
    {
        foreach ($files as $file) {
            if (empty($file)) {
                continue;
            }
            if (preg_match_all('/^([\w\/])*([\.\w]+)$/', $file)) {  //查找有后缀的文件名称是否存在中文，空格，'－'
                continue;
            } elseif (preg_match_all('/^([\w\/])*$/', $file)) { //查找无后缀的文件名称是否存在中文，空格，'－'
                continue;
            } else {
                $this->outputErrorMessage('svn://192.168.1.2/cooking_docs/trunk/3-美术目录/3-程序用资源/” 目录下，文件名不能出现“空格“，中文，”-“ :该文件名不符合格式' . $file, $output);
            }
        }
    }

    /**
     * 检测字段值中的图片是否存在
     * @param array $csvArrs
     * @param array $files
     * @param OutputInterface $output
     */
    private function checkEmptybuildingimg(array $csvArrs, array $files, outputInterface $output)
    {
        //要查找的图片
        $searchFiles = [];
        $dirName = "SCENE_OBJECTS";
        $searchCSVs = [];
        //筛选需要查找的图片
        foreach ($csvArrs as $csvArr) {
            //过滤掉id=190003
            if ($csvArr['id'] == '190003') {
                continue;
            }
            if ($csvArr['subtype'] == '9') {  //subtype等于９
                $fileNames = $csvArr['buildingimg'];
                $searchFile = $dirName . DIRECTORY_SEPARATOR . $fileNames . "_1_door_1.png";
                $searchFiles[] = $searchFile;       //存储需要查找的文件路径
                $searchCSVs [$searchFile] = $csvArr;
                $searchFile = $dirName . DIRECTORY_SEPARATOR . $fileNames . "_1_door_0.png";
                $searchFiles[] = $searchFile;
                $searchCSVs[$searchFile] = $csvArr;
            }
        }

        //查找图片
        foreach ($searchFiles as $searchFile) {
            if (!in_array($searchFile, $files)) {
                $totalFields = $searchCSVs[$searchFile];
                $this->outputErrorMessage("id:" . $totalFields['id'] . ',当subtype等于９时，这个命名不存在buildimg字段值中:' . $searchFile, $output);
            }
        }

    }

    /**
     * 检测图片名称开头是否是：icon_
     * @param array $files
     * @param OutputInterface $output
     */
    private function checkImageNamesPrefix(array $files, outputInterface $output)
    {

        $filePath = "UI" . DIRECTORY_SEPARATOR . "icon/";
        foreach ($files as $file) {
            if (util::starts_with($file, $filePath)) { //获取到路径下的所有文件
                $flashKey = "UI" . DIRECTORY_SEPARATOR . "icon" . DIRECTORY_SEPARATOR . "icon_";
                if (!util::starts_with($file, $flashKey)
                    && !util::ends_with($file, "/")
                ) {   //对文件名进行判断
                    $this->outputErrorMessage("该路径下：svn://192.168.1.2/cooking_docs/trunk/3-美术目录/3-程序用资源/UI/icon/，图片名称无前缀icon_:" . $file, $output);
                }
            }
        }

    }

    /**
     * 检测文件名称中是否存在下划线_
     * @param array $files
     * @param OutputInterface $output
     */
    private function checkFilenamesContainsUnderline(array $files, outputInterface $output)
    {
        foreach ($files as $file) {
            if (empty($file)) { //过滤空值
                continue;
            } elseif (util::ends_with($file, "/")) {  //过滤文件夹
                continue;
            }
            $fileNames = basename($file);
            if (strstr($fileNames, '_') == null) {
                $this->outputErrorMessage("路径：svn://192.168.1.2/cooking_docs/trunk/3-美术目录/3-程序用资源，文件名无下划线:" . $file, $output);
            }
        }
    }

    /**
     * 检测路径中是否存在，以表013007.客户端特效配置$client_ui_animation_config$c.csv中的animname字段值，命名的文件，文件名称：ct_animation_字段值.fla
     * @param array $files
     * @param OutputInterface $output
     */
    private function checkAnimationFileName(array $files, outputInterface $output)
    {
        $svnFileName = '013007.客户端特效配置$client_ui_animation_config$c.csv';
        $svnUrl = 'svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格/' . $svnFileName;
        $csvArrs = $this->SVNCatCsv($svnUrl);
        //查询csv表格中是否存在
        foreach ($csvArrs as $csvArr) {
            $fileName = 'Animation' . DIRECTORY_SEPARATOR . 'ct_animation_' . $csvArr['animname'] . '.fla';
            if (!in_array($fileName, $files)) {
                $this->outputErrorMessage('检测路径：svn://192.168.1.2/cooking_docs/trunk/3-美术目录/3-程序用资源/Animation/下,不存在这个文件:' . $fileName, $output);
            }
        }

    }
}