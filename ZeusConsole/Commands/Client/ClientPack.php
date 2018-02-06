<?php
namespace ZeusConsole\Commands\Client;

use CFPropertyList\CFPropertyList;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Yaml\Yaml;
use utilphp\util;
use Vlaswinkel\Lua\LuaInputStream;
use Vlaswinkel\Lua\LuaParser;
use Vlaswinkel\Lua\LuaTokenStream;
use Vlaswinkel\Lua\LuaToPhpConverter;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Exceptions\exception;
use ZeusConsole\Utils\svn;
use ZeusConsole\Utils\utils;

/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/10/10
 * Time: 上午11:39
 */
class ClientPack extends CommandBase
{

    /**
     * 本地打包列表文件路径
     * @var string
     */
    private $localJsonPackDataPath = '';

    /**
     * tag原路径
     * @var string
     */
    private $tagSourcePath = 'svn://192.168.1.2/project/code/clientcocos';

    /**
     * @var string
     */
    private $tagDestPath = 'svn://192.168.1.2/project/tags/codes/';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('client:pack')->setDescription('打包客户端程序');
        $this->addOption('clientSvnPath', null, InputOption::VALUE_OPTIONAL, '客户端程序所在的svn路径',
            'svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources');

        $this->addOption('clientSvnPackFile', null, InputOption::VALUE_OPTIONAL, '客户端打包历史文件svn路径',
            'svn://192.168.1.2/cooking_packages/trunk/clientUpgradePackageInfo/packagelist.json');

        $this->addOption('clientVersionSvnPath', null, InputOption::VALUE_OPTIONAL, '客户端版本文件所在的svn路径',
            'svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YY/appversion.lua');

        $this->addOption('clientCppVersionSvnPath', null, InputOption::VALUE_OPTIONAL, 'cpp 版本所在svn路径 YYConstPlatform.h',
            'svn://192.168.1.2/project/code/clientcocos/scripting/lua/cocos2dx_support/LuaYYEngine/YYConstPlatform.h');

        $this->addOption('packOldestVersion', 'p', InputOption::VALUE_OPTIONAL, '向前递归的版本号,默认为全部版本,目的版本号格式 x_x_x 例如:1_0_53');
        $this->addOption('autoUploadPackages', null, InputOption::VALUE_NONE, '增量安装包是否自动上传的到远程服务器,目前是7牛');

//        $this->addOption('export', null, InputOption::VALUE_OPTIONAL, '本次打包保存的文件路径', utils::getTempDirectoryPath());


        $this->addOption('checkMode', null, InputOption::VALUE_NONE, '检测模式,如果是检测模式,只检测是否可以打包,不进行实质性打包');
        $this->addOption('ignoreCheckCppVersion', null, InputOption::VALUE_NONE, '忽略检测CPP版本');


        $this->addOption('dailyPack', null, InputOption::VALUE_NONE, '是否是日常打包.日常打包不tag..');
        $this->addOption('maxPackCount', null, InputOption::VALUE_OPTIONAL, '最大打包数量,0为全部符合规则的包', 0);
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $fs = new Filesystem();

        $fs->remove($this->getClientPackRootPath());
        $fs->mkdir($this->getClientPackRootPath());

        $fs->remove($this->getUploadZipPath());
        $fs->mkdir($this->getUploadZipPath());

        $fs->remove($this->getSVNSrcResourcePath());
        $fs->mkdir($this->getSVNSrcResourcePath());

        $fs->remove($this->getSVNRestoreResourcePath());
        $fs->mkdir($this->getSVNRestoreResourcePath());


    }


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
        $clientSvnPath = $input->getOption('clientSvnPath');
        //最新的SVN版本号
        $newestRevision = utils::getSvnRevision($clientSvnPath);

        $clientVersionSvnPath = $input->getOption('clientVersionSvnPath');
        //需要打包的客户端版本号
        $packageClientVersion = $this->getPackVersion($clientVersionSvnPath);

        //是否是日常打包
        $dailyPack = $input->getOption('dailyPack');


        $cppVersion = $this->getCppVersion($input->getOption('clientCppVersionSvnPath'));

        $ignoreCheckCppVersion = $input->getOption('ignoreCheckCppVersion');
        if (!$ignoreCheckCppVersion) {
            //cpp版本不一致,需要打ipa
            $plistCppVersion = $this->getCppVersionFormPlist();
            if ($cppVersion !== $plistCppVersion) {
                $output->writeln("<error>cpp版本不一致,cpp:$cppVersion,plist:$plistCppVersion</error>");
                return 1;
            }
        }

        //csv资源的svn版本号
        $csvSvnRevision = $this->getCSVResourceRevision($newestRevision);

        $output->writeln([
            '本次打包的客户端信息:',
            '打包零时路径:<info>' . $this->getClientPackRootPath() . '</info>',
            'svn版本号:<info>' . $newestRevision . '</info>',
            '客户端版本号:<info>' . $packageClientVersion . '</info>',
            '客户端C++版本号:<info>' . $cppVersion . '</info>',
            '资源版本号:<info>' . $csvSvnRevision . '</info>'
        ]);


        $clientSvnPackFile = $input->getOption('clientSvnPackFile');
        //导出版本控制文件
        $this->exportPackageList($clientSvnPackFile);
        /**
         * @var ClientPackData[] $packageInfos
         */
        $packageInfos = $this->getPackRevisions();


        //每个cpp版本对应的第一个客户端版本
        $cppPackages = $this->getCppFirstPack($packageInfos);


        //翻转 最后的包在最上面
        $packageInfos = array_reverse($packageInfos);
        foreach ($packageInfos as $packageInfo) {
            /**
             * @var $packageInfo ClientPackData
             */
            if ($packageClientVersion === $packageInfo->getClientversion()) {
                $output->writeln('<error>客户端版本已经存在:' . $packageClientVersion . '</error>');
                $output->writeln('<error>需要修改:' . $clientVersionSvnPath . '中的客户端版本号</error>');
                return 1;
            }
        }

        $checkMode = $input->getOption('checkMode');
        if ($checkMode) {
            $output->writeln('<info>检测完成!</info>');
            return 0;
        }


        $output->write('开始从SVN: ' . $clientSvnPath . '导出最新客户端 ');
        //导出最新的客户端版本到本地缓存
        $process = utils::createSvnProcess([
            'export',
            '--force',
            '-r',
            $newestRevision,
            $clientSvnPath,
            $this->getSVNSrcResourcePath()
        ]);
        $process->run();


        $output->writeln('<info>导出完成!!!</info>');


        $packOldestVersion = $input->getOption('packOldestVersion');
        $restorePackageInfo = null;
        $maxPacksCount = intval($input->getOption('maxPackCount')) == 0 ? 0xFFFF : intval($input->getOption('maxPackCount'));
        //当前被打包的列表
        $packedList = [];

        //打包函数
        $packIncrement = function (ClientPackData $packageInfo) use ($output, $newestRevision, $packageClientVersion, $clientSvnPath) {
            $output->write('正在打包' . $packageInfo->getClientversion() . ' to ' . $packageClientVersion . " ... ");
            $fromRevision = $packageInfo->getTo();
            $this->svnDiffAndZip($clientSvnPath, $fromRevision, $newestRevision);
            $resourceFileName = $this->makeUploadZip($packageInfo->getClientversion(), $packageClientVersion);
            $output->writeln([
                    '<info>打包完成!!!</info>',
                    '文件名:<info>' . basename($resourceFileName) . '</info>',
                    '文件大小:<info>' . floor(filesize($resourceFileName) / 1024) . 'K</info>'
                ]
            );
        };

        foreach ($packageInfos as $packageInfo) {
            //两包一致,则不打
            if ($packageInfo->getClientversion() == $clientVersionSvnPath) {
                continue;
            }
            //原来的C++版本 和 当前的C++版本不符合,则不用打包了
            //说明上一个包是发的完整安装包
            if ($packageInfo->getCppversion() != $cppVersion) {
                break;
            }
            //记录打包
            $packedList[$packageInfo->getClientversion()] = $packageInfo;

            //还原到的版本号
//            if (is_null($restorePackageInfo)) {
//                $restorePackageInfo = $packageInfo;
//            }
            $packIncrement($packageInfo);
            //到达历史打包最大数量
            if (!empty($packOldestVersion) &&
                $packOldestVersion == $packageInfo->getClientversion()
            ) {
                break;
            }
            //打包数量最大化
            if (count($packedList) >= $maxPacksCount) {
                break;
            }
        }

        if (isset($cppPackages[$cppVersion])) {

            $cppPackageInfo = $cppPackages[$cppVersion];
            //需要打原始包的最终包的增量包
            if (!isset($packedList[$cppPackageInfo->getClientversion()])) {
                $packIncrement($cppPackageInfo);
                $packageName = $this->upgradeList[$cppPackageInfo->getClientversion()];

//            var_dump($packageInfos);
                //补足其它包的新包的增量包
                foreach ($packageInfos as $packageInfo) {
                    if ($packageInfo->getCppversion() != $cppVersion) {
                        break;
                    }
                    if ($packageInfo->getCppversion() == $cppPackageInfo->getClientversion()) {
                        break;
                    }
                    //已经通过标准的增量包打过次包了
                    if (isset($packedList[$packageInfo->getClientversion()])) {
                        continue;
                    }

                    $this->upgradeList[$packageInfo->getClientversion()] = $packageName;
                }
            }
        }


//        var_dump($this->upgradeList);
//        return 1;


        //打还原包
        if (!is_null($restorePackageInfo)) {
            //导出还原包的SVN数据

            $fromRevision = $restorePackageInfo->getTo();
            $process = utils::createSvnProcess([
                'export',
                '--force',
                '-r',
                $fromRevision,
                $clientSvnPath,
                $this->getSVNRestoreResourcePath()
            ]);
            $process->run();


            $this->svnDiffAndZip($clientSvnPath, $newestRevision, $fromRevision);
            $resourceFileName = $this->makeRestoreUploadZip($packageClientVersion, $restorePackageInfo->getClientversion());
            $output->writeln([
                    '<info>打包还原包完成!!!</info>',
                    '文件名:<info>' . basename($resourceFileName) . '</info>',
                    '文件大小:<info>' . (filesize($resourceFileName) / 1024) . 'k</info>'
                ]
            );

            $fs = new Filesystem();
            $fs->remove($this->getSVNRestoreResourcePath());

        }


        $output->writeln('<info>打包完成</info>');


        if ($input->getOption('autoUploadPackages')) {
            $output->writeln('<info>开始上传...</info>');
            if (!$this->upload()) {
                $output->writeln('<error>上传失败!</error>');
                return 2;
            }
            $output->writeln(['<info>安装包上传成功</info>', '开始写入版本信息...']);
        }
        //开始生成php版本信息
        $packageInfos = $this->getPackRevisions();

        $this->dumpUpgradeInfo($packageInfos, $packageClientVersion);


//        return;
        //新的打包信息
        $newPackData = new ClientPackData();
        $newPackData->setId(util::random_string())
            ->setFrom('0')
            ->setTo($newestRevision)
            ->setCppversion($cppVersion)
            ->setClientversion($packageClientVersion)
            ->setPacktime(date('Y-m-d H:i:s'))
            ->setCsvSvnRevision($csvSvnRevision);


        //写入新的打包信息
        $packageInfos[] = $newPackData;


        $output->writeln('开始保存打包文件');
        $this->backUpClientPackFilesToSvn($packageClientVersion);


        if (!$dailyPack) {
            //不是日常打包
            //tag客户端
            svn::tag($this->tagSourcePath,
                $this->tagDestPath . "lua($packageClientVersion)cpp($cppVersion)csv($csvSvnRevision)",
                "commit increase Package clientVersion:" . $packageClientVersion);
        }

        $output->writeln('<info>写入版本信息成功!</info>');
        $this->savePackRevisions($packageInfos, $packageClientVersion,
            $newestRevision,
            $csvSvnRevision);

        $output->writeln('<info>打包完成!</info>');
    }

    /**
     * 最终文件的导出文件夹
     */
    private function getExportPath()
    {
        return $this->getClientPackRootPath() . 'export/';
    }

    /**
     * 获取上传文件的路径
     * @return string
     */
    private function getUploadZipPath()
    {
        return $this->getExportPath() . 'packageUploads';
    }

    /**
     * 返回还原包路径
     * @return string
     */
    private function getRestoreZipPath()
    {
        return $this->getExportPath() . 'packageRestore';
    }

    private function getSVNSrcResourcePath()
    {
        return $this->getClientPackRootPath() . 'srcResource';
    }

    private function getSVNRestoreResourcePath()
    {
        return $this->getExportPath() . 'restoreResource';
    }

    private function getSVNPackageListPath()
    {
        return $this->getClientPackRootPath() . 'svnPackageList';
    }

    private function getClientPackRootPath()
    {
        return utils::getTempDirectoryPath() . 'clientPack' . DIRECTORY_SEPARATOR;
    }


    /**
     * 导出打包版本号文件
     * @param $svnPath
     */
    private function exportPackageList($svnPath)
    {
        $svnPackageDirectory = $this->getSVNPackageListPath();
        $this->localJsonPackDataPath = $svnPackageDirectory . DIRECTORY_SEPARATOR . basename($svnPath);
        $fs = new Filesystem();
        $fs->remove($svnPackageDirectory);
        $fs->mkdir($svnPackageDirectory);


        $process = utils::createSvnProcess([
            'checkout',
            pathinfo($svnPath, PATHINFO_DIRNAME),
            $svnPackageDirectory
        ]);
        $process->run();

        if ($this->verboseDebug) {
            var_dump($svnPackageDirectory);
            var_dump($process->getOutput());
            var_dump($process->getErrorOutput());
        }

        $process = utils::createSvnProcess([
            'up',
            pathinfo($svnPath, PATHINFO_BASENAME),
        ]);
        $process->setWorkingDirectory($svnPackageDirectory);
        $process->run();

        if ($this->verboseDebug) {
            var_dump($process->getOutput());
            var_dump($process->getErrorOutput());
        }


    }

    /**
     * 获取所有打包的版本号
     * @return ClientPackData[]
     */
    private function getPackRevisions()
    {
        $contents = file_get_contents($this->localJsonPackDataPath);
        $packDatas = utils::getSerializer()->deserialize($contents, 'ZeusConsole\Commands\Client\ClientPackData[]', 'json');
        return $packDatas;


    }

    /**
     * @param ClientPackData[] $packages
     * @return ClientPackData[]
     */
    private function getCppFirstPack(array $packages)
    {
        $cppPackages = [];

//        $prevCppVersion = null;
        $prevPackage = null;
        foreach ($packages as $package) {
            //第一个版本
            if (is_null($prevPackage)) {
                $prevPackage = $package;
                $cppPackages[$prevPackage->getCppversion()] = $prevPackage;
            }
            //前面的版本和后面的版本C++版本不一致
            //把后面的第一个版本作为C++的第一个lua版本处理
            if ($prevPackage->getCppversion() != $package->getCppversion()) {
                $cppPackages[$package->getCppversion()] = $package;
                $prevPackage = $package;
            }
        }

        return $cppPackages;
    }

    /**
     * 保存打包信息
     * @param array $infos
     * @param $newVersion
     * @param $clientSvnVersion
     * @param $csvSvnVersion
     */
    private function savePackRevisions(array $infos, $newVersion,
                                       $clientSvnVersion,
                                       $csvSvnVersion)
    {
//        utils::getSerializer()->normalize()
        $str = utils::getSerializer()->serialize($infos, "json", ['json_encode_options' => JSON_PRETTY_PRINT]);
        $fs = new Filesystem();
        $fs->dumpFile($this->localJsonPackDataPath, $str);

        $process = utils::createSvnProcess([
            'commit',
            pathinfo($this->localJsonPackDataPath, PATHINFO_BASENAME),
            '-m',
            "commit increase PackageInfo clientVersion:" . $newVersion .
            " clientSvnRevision:" . $clientSvnVersion .
            " csvSvnRevision:" . $csvSvnVersion
        ]);
        $process->setWorkingDirectory($this->getSVNPackageListPath());
        $process->run();


//        var_dump($process->getOutput());
//        var_dump($process->getErrorOutput());
    }

    /**
     * 获取将要打包的资源版本号
     * @param $svnPath
     * @return string
     */
    private function getPackVersion($svnPath)
    {
        $process = utils::createSvnProcess([
            'cat',
            $svnPath,
        ]);
        $process->run();

//        var_dump($process->getOutput());


        $lines = explode("\n", $process->getOutput());
        foreach ($lines as $line) {
            if (util::starts_with($line, 'APP_VERSION = ')) {
                $Version = explode(" ", $line)[2];
                $Version = trim($Version);
                $Version = trim($Version, "\"");
//                $Version = strtr($Version, ["." => '_']);
//                var_dump($Version);
                return $Version;
            }
        }
        return "";

    }

    /**
     * 通过Plist获取客户端版本号
     * @return mixed
     * @throws \CFPropertyList\IOException
     * @throws \CFPropertyList\PListException
     * @throws \DOMException
     */
    private function getCppVersionFormPlist()
    {
        $plistVersionFile = "svn://zhipeng@192.168.1.2/project/code/clientcocos/projects/YYGame/proj.ios/Info.plist";
        $process = utils::createSvnProcess([
            'cat',
            $plistVersionFile
        ]);
        $process->run();


        $plist = new CFPropertyList();
        $plist->parse($process->getOutput(), CFPropertyList::FORMAT_AUTO);


        $plistArray = $plist->toArray();
        return $plistArray['CFBundleVersion'];
    }

    /**
     * 获取Cpp版本号
     * @param $svnPath
     * @return string
     */
    private function getCppVersion($svnPath)
    {


        $process = utils::createSvnProcess([
            'cat',
            $svnPath
        ]);
        $process->run();


        $lines = explode("\n", $process->getOutput());
        foreach ($lines as $line) {
            if (util::starts_with($line, 'static const char * CC_CPP_VERSION = ')) {
                $Version = explode("=", $line)[1];
                $Version = trim($Version);
                $Version = trim($Version, ';');
                $Version = trim($Version);
                $Version = trim($Version, "\"");
                return $Version;
            }
        }
        return "";

    }


    private function svnDiffAndZip($svnPath, $from, $to = -1)
    {
        $packagePath = $this->getClientPackRootPath() . 'Resources';
        $fs = new Filesystem();
        $fs->remove($packagePath);

        $process = utils::createSvnProcess([
            'diff',
            '--summarize',
            '-r',
            $from . ':' . $to,
            $svnPath,
        ]);
        $process->run();


        $lines = explode("\n", $process->getOutput());

//        var_dump([$from, $to, $lines]);

        /**
         * 是否从svn导出差异
         */
        $fromSVN = intval($from) > intval($to);

        foreach ($lines as $line) {
            $line = ltrim($line, ' ');
            if (empty($line) || util::starts_with($line, 'D')) {
                continue;
            }
            //SVN 文件路径

            $svnIndex = stripos($line, 'svn://');;
            $svnFileName = substr($line, $svnIndex);

//            var_dump($svnFileName);

            //本地文件路径
            $localFileName = str_replace($svnPath, $packagePath, $svnFileName);
            $localdirfileName = pathinfo($localFileName, PATHINFO_DIRNAME);
            //创建文件夹
            $fs->mkdir($localdirfileName);
            //如果是目录不处理
            if (is_dir($localFileName)) {
                continue;
            }


            if ($fromSVN) {
                $localSrcFileName = str_replace($svnPath, $this->getSVNRestoreResourcePath(), $svnFileName);
                $fs->copy($localSrcFileName, $localFileName);

            } else {
                //从本地拷贝对应的文件
                $localSrcFileName = str_replace($svnPath, $this->getSVNSrcResourcePath(), $svnFileName);
                $fs->copy($localSrcFileName, $localFileName);
            }

        }

        $fs->remove($this->getClientPackRootPath() . 'Resource.zip');

        //压缩

        $processBuilder = new ProcessBuilder();
        $processBuilder->setWorkingDirectory($this->getClientPackRootPath());
        $processBuilder->setPrefix('zip');
        $processBuilder->setArguments(
            [
                '-r',
                'Resources.zip',
                'Resources',

            ]);

        $process = $processBuilder->getProcess();
        $process->run();

        $fs->remove($packagePath);
    }

    private $uploadList = [];

    /**
     * 创建版本文件
     * @param $fromVersion
     * @param $toVersion
     * @return string
     */
    private function makeUploadZip($fromVersion, $toVersion)
    {
        $fs = new Filesystem();
        $newFileName = $this->getUploadZipPath() .
            DIRECTORY_SEPARATOR .
            'Resources_' .
            $fromVersion .
            '_to_' .
            $toVersion .
            '.zip';

        $fs->rename($this->getClientPackRootPath() . 'Resources.zip',
            $newFileName);

        $this->uploadList[$fromVersion] = $newFileName;

        $this->setUpgradeInfo($fromVersion, $newFileName);

        return $newFileName;

    }

    private $upgradeList = [];

    /**
     * 设置更新信息
     * @param $fromClientVersion
     * @param $upgradeFileName
     */
    private function setUpgradeInfo($fromClientVersion, $upgradeFileName)
    {
        $this->upgradeList[$fromClientVersion] = $upgradeFileName;
    }

    /**
     * 打包还原包
     * @param $fromVersion
     * @param $toVersion
     * @return string
     */
    private function makeRestoreUploadZip($fromVersion, $toVersion)
    {
        $fs = new Filesystem();
        $newFileName = $this->getRestoreZipPath() .
            DIRECTORY_SEPARATOR .
            'Restore_Resources_' .
            $fromVersion .
            '_to_' .
            $toVersion .
            '.zip';

        $fs->mkdir($this->getRestoreZipPath());

        $fs->rename($this->getClientPackRootPath() . 'Resources.zip',
            $newFileName);

        $this->uploadList[$fromVersion] = $newFileName;

        return $newFileName;
    }

    /**
     * 上传文件
     * @return bool
     * @throws \Exception
     */
    private function upload()
    {
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = 'F5aWHx-yKKt_AJQpkba-auWYLu_43hoyllxNIe82';
        $secretKey = 'mQd4sD4Scz5n4Wv9G7o2pv0S1ry1q471wnih1hep';

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = 'tomatofuns-app-package';

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);


        $bucketMgr = new BucketManager($auth);

        // 要上传文件的本地路径
        foreach ($this->uploadList as $uploadFileName) {
            $filePath = $uploadFileName;
            echo '正在上传文件:' . $uploadFileName . "\n";

            // 上传到七牛后保存的文件名
            $key = basename($uploadFileName);

            //删除已经存在的资源文件
            $bucketMgr->delete($bucket, $key);
            // 初始化 UploadManager 对象并进行文件的上传。
            $uploadMgr = new UploadManager();
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            echo "上传结果: \n";
            if ($err !== null) {
                var_dump($err);
                return false;
            } else {
                var_dump($ret);
            }
        }
        return true;
    }

    /**
     * 保存升级信息
     * @param array $packageInfos
     * @param $destClientVersion
     */
    private function dumpUpgradeInfo(array $packageInfos = [], $destClientVersion)
    {
        $templateFilePath = __DIR__ . DIRECTORY_SEPARATOR . "Templates" . DIRECTORY_SEPARATOR . 'UpgradeCell.rtf';
        $cellTemplateString = file_get_contents($templateFilePath);
        $cellString = "";
        foreach ($packageInfos as $packageInfo) {
            /**
             * @var $packageInfo ClientPackData
             */
            $needupgradecpp = !isset($this->upgradeList[$packageInfo->getClientversion()]);
            $cellString .= translator()->trans($cellTemplateString,
                [
                    "{ clientfromversion }" => $packageInfo->getClientversion(),
                    "{ needupgradecpp }" => ($needupgradecpp ? "true" : "false"),
                    "{ upgradefilename }" => ($needupgradecpp ? "updatecpp" : basename($this->upgradeList[$packageInfo->getClientversion()])),
                    "{ clienttoversion }" => $destClientVersion,
                    "{ size }" => ($needupgradecpp ? '-1' : filesize($this->upgradeList[$packageInfo->getClientversion()]))

                ]);
        }

        $cellString = rtrim($cellString, ',');

        $templateFilePath = __DIR__ . DIRECTORY_SEPARATOR . "Templates" . DIRECTORY_SEPARATOR . 'UpgradeVersions.rtf';
        $VersionsTemplateString = file_get_contents($templateFilePath);
        $upgradeString = translator()->trans($VersionsTemplateString,
            [
                "{{ versions }}" => $cellString
            ]);


        $this->clientUpgradeVersionFilePath = $this->getExportPath() . "upgradeversions.php";
        $fs = new Filesystem();
        $fs->dumpFile($this->clientUpgradeVersionFilePath, $upgradeString);
    }

    private $clientUpgradeVersionFilePath = "";


    /**
     * @param $packageClientVersion
     * @param string $backupPath 备份路径
     */
    private function backUpClientPackFilesToSvn($packageClientVersion)
    {
        echo "开始备份 " . $packageClientVersion . " 升级文件 ... \n";
        //压缩

//        $backupName = 'Resources_backup_' . $packageClientVersion . '_' . date('Ymd-His');
//        echo "备份文件名:" . $backupName . ".zip\n";
//        echo "备份路径:" . $backupPath . "\n";
//        $processBuilder = new ProcessBuilder();
//        $processBuilder->setWorkingDirectory($backupPath);
//        $processBuilder->setPrefix('zip');
//        $processBuilder->setArguments(
//            [
//                '-r',
//                $backupName,
//                'clientPack',
//            ]);
//        $process = $processBuilder->getProcess();
//        $process->run();
//
//        echo "本地备份完成\n";

        echo "开始备份到SVN\n";

        $process = utils::createSvnProcess([
            'import',
            $this->getExportPath(),
            "svn://192.168.1.2/cooking_packages/trunk/clientUpgradePackages/" . $packageClientVersion,
            '-m',
            'commit increase Package clientVersion:' . $packageClientVersion
        ]);
        $process->run();

        var_dump($process->getOutput());

        echo "备份完成! \n";
    }

    /**
     * 获取csv资源版本号
     * @param $clientRevision
     * @return null
     */
    private function getCSVResourceRevision($clientRevision)
    {
        //lua包中资源的版本号
        $luaCsvSvnPath = "svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YY/Game/json/csv_resources_setting.lua";

        $process = utils::createSvnProcess([
            'cat',
            $luaCsvSvnPath,
            '-r',
            $clientRevision
        ]);
        $process->run();

        if ($process->getExitCode()) {

//            assert(false, "客户端CSV资源版本号无效");
            throw new exception("客户端CSV资源版本号无效");
        }

        $luaCvsSvnContentTotal = $process->getOutput();
        $luaCvsSvnContents = explode("\n", $luaCvsSvnContentTotal);
        $luaTableContent = "";
        $luaTableContentStart = false;

        foreach ($luaCvsSvnContents as $luaCvsSvnContent) {

            $attachContent = $luaCvsSvnContent;
            if (util::starts_with($luaCvsSvnContent, "local config_csv_resources_setting = {")) {
                $luaTableContentStart = true;
                $attachContent = "{";
            } elseif (util::starts_with($luaCvsSvnContent, "}")) {
                $luaTableContentStart = false;
            }
            if ($luaTableContentStart) {
                $luaTableContent .= $attachContent;
            }
        }

        $luaTableContent .= "}";
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream($luaTableContent)));
        $node = $parser->parse();
        $results = LuaToPhpConverter::convertToPhpValue($node);
//        var_dump($results);

//        var_dump($luaTableContent);


        foreach ($results as $result) {
            if ($result["key"] == "SVNRevision") {
                return $result["value"];
            }
        }
        return null;
    }

}
