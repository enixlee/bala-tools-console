<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/10/13
 * Time: 上午11:32
 */

namespace ZeusConsole\Commands\Client;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\ProcessBuilder;
use utilphp\util;
use Vlaswinkel\Lua\LuaInputStream;
use Vlaswinkel\Lua\LuaParser;
use Vlaswinkel\Lua\LuaTokenStream;
use Vlaswinkel\Lua\LuaToPhpConverter;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Exceptions\exception;
use ZeusConsole\Utils\svn;
use ZeusConsole\Utils\utils;

class ClientBuildIOSFromSvn extends CommandBase
{

    const PLATFORM_KEY_CHANNEL_NAME = 'CHANNEL_NAME';
    const PLATFORM_KEY_SCHEME = 'SCHEME';
    const ProvisioningProfile = 'ProvisioningProfile';

    /**
     * 渠道信息
     * @var array
     */
    private $PLATFORM_INFO = [
        'AppStore' => [
            self::PLATFORM_KEY_CHANNEL_NAME => 'CT_Platform_APPSTORE',
            self::PLATFORM_KEY_SCHEME => 'YYGameAppstore',
            self::ProvisioningProfile => 'dreamwork-distribution'
        ],
        'Inhouse' => [
            self::PLATFORM_KEY_CHANNEL_NAME => 'CT_Platform_INHOUSE',
            self::PLATFORM_KEY_SCHEME => 'YYGameInhouse',
            self::ProvisioningProfile => 'ctgame_inhouse'
        ],
        'InhouseTest' => [
            self::PLATFORM_KEY_CHANNEL_NAME => 'CT_Platform_FANQIE_DEBUG_Phone',
            self::PLATFORM_KEY_SCHEME => 'YYGameInhouseTest',
            self::ProvisioningProfile => 'ctgame_inhouse'
        ]
    ];

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('client:buildIOSFromSvn');
        $this->setAliases([
            'client:buildIOS'
        ]);
        $this->setDescription('编译客户端完整包');
        $this->addArgument('clientSourceSvn', InputArgument::OPTIONAL, "客户端所在目录,最后是clientcocos,例如:\n
        ", "svn://192.168.1.2/project/code/clientcocos");

        $this->addOption('platformName', null, InputOption::VALUE_OPTIONAL, "渠道信息:
        all:为全部渠道
        AppStore:线上苹果商店,Inhouse:线上发布,InhouseTest内网真机测试", "InhouseTest");

        $this->addOption('sameCppVersion', null, InputOption::VALUE_NONE, "是否是相同的CPP版本,如果相同,说明C++
        代码没有更新,只是打包,其它渠道增量包就可以了.");
//        $this->addOption('scheme', null, InputOption::VALUE_OPTIONAL, 'IOS编译Scheme 默认为 YYGameInhouse', 'YYGameInhouse');

        $this->addOption('testMode', null, InputOption::VALUE_NONE, '是否为测试模式,如果是测试模式,不检测是否存在安装包,不上传SVN');
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
        $fs = new Filesystem();
        $fs->remove($this->getBuildDirectory());
        $fs->mkdir($this->getBuildDirectory());
        $fs->mkdir($this->getIpaPath());

        parent::initialize($input, $output);
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


        $svnProjectPath = $input->getArgument('clientSourceSvn');
        $platform = $input->getOption("platformName");
        $testMode = $input->getOption('testMode');
        $sameCppVersion = $input->getOption("sameCppVersion");


        $cppVersion = $this->getCppVersion();
        $luaVersion = $this->getLuaCodeVersion();
        $csvVersion = $this->getCSVResourceRevision();



        $output->writeln([
            "当前需要打包的程序信息",
            "lua版本号:$luaVersion",
            "cpp版本号:$cppVersion",
            "csv资源版本号:$csvVersion"
        ]);

        /**
         * @var QuestionHelper $helper
         */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("是否继续打包?? (y/n):", false);
        $bundle = $helper->ask($input, $output, $question);
        if (!$bundle) {
            $output->writeln("<error>放弃打包</error>");
            return 0;
        }


        if (!$testMode) {
            // 测试模式不检测环境
            $error = $this->checkEnvironment($input, $output);
            if ($error) {
                return $error;
            }

//            return $error;


            //测试模式不打增量包打空包
            $error = $this->createEmptyPackageData($input, $output);
            if ($error) {
                return $error;
            }


            //增加tag
            $svnProjectPath = $this->tag();

        }


        $error = $this->updateCodeFromSvn($svnProjectPath, -1, $output);
        if ($error) {
            $output->writeln([
                '<error>从SVN更新失败</error>'
            ]);
            return $error;
        }

        if ($platform == "all") {
            $platforms = array_keys($this->PLATFORM_INFO);
        } else {
            $platforms = [$platform];
        }

        foreach ($platforms as $platformId) {
            $error = $this->createIpa($this->getBuildSourceDirectory(), $platformId, $output);
            if ($error) {
                return $error;
            }
        }


        if (!$testMode) {
            //测试模式不备份结果到SVN
            $this->backUpIpaToSvn($output);


            //tag客户端

        }

        $output->writeln("<info>打包完成</info>");
    }


    /**
     * 检查编译环境
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    private function checkEnvironment(InputInterface $input, OutputInterface $output)
    {
        $cppVersion = $this->getCppVersion();
        $luaVersion = $this->getLuaCodeVersion();


        $sameCppVersion = $input->getOption("sameCppVersion");

        $clientPackDatas = svn::cat('svn://192.168.1.2/cooking_packages/trunk/clientUpgradePackageInfo/packagelist.json');

        $clientPackDatas = utils::getSerializer()->deserialize($clientPackDatas,
            'ZeusConsole\Commands\Client\ClientPackData[]', 'json');

        foreach ($clientPackDatas as $clientPackData) {
            /**
             * @var $clientPackData ClientPackData
             */
            if (!$sameCppVersion) {
                if ($clientPackData->getCppversion() === $cppVersion) {
                    $output->writeln("<error>cpp版本号已经存在:$cppVersion</error>");
                    return 1;
                }
            }

            if ($clientPackData->getClientversion() === $luaVersion) {
                $output->writeln("<error>lua版本号已经存在:$luaVersion</error>");
                return 1;
            }

        }

        return 0;
    }

    /**
     * @param $svnPath
     * @param $svnRevision 0 为最新
     * @param OutputInterface $output
     * @return string svn导出目录
     */
    private function updateCodeFromSvn($svnPath, $svnRevision, OutputInterface $output)
    {
        if ($svnRevision <= 0) {
            $svnRevision = "HEAD";
        }
        $process = utils::createSvnProcess([
            'export',
            $svnPath,
            $this->getBuildSourceDirectory(),
            '--force',
            '-r',
            $svnRevision
        ]);
        $process->setTimeout(null);
        $output->writeln(
            [
                "开始从SVN导出源代码",
                $svnPath
            ]);
        $process->run();

        if ($this->isVerboseDebug()) {
            $output->write($process->getOutput());
        }
        if ($process->getExitCode()) {
            $output->writeln(["从SVN导出客户端错误:", $svnPath]);
            return 1;
        }
        $output->writeln(["导出SVN代码完成!",
            "from:",
            $svnPath,
            "to:",
            $this->getBuildSourceDirectory()]);

        return 0;
//        return $this->getBuildDirectory();
    }

    /**
     * 创建空的增量包信息,主要是增加发布记录
     * 增量更新包
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    private function createEmptyPackageData(InputInterface $input, OutputInterface $output)
    {
        //打一个C++变更的空增量包,主要是增加发布记录
        $ClientPack = $this->getApplication()->find('client:pack');
        //是否是相同的cpp版本的一个ipa
        $sameCppVersion = $input->getOption("sameCppVersion");

        $ClientPackParams = [
            'command' => 'csv:pack',
        ];
        // 如果cpp版本和上一次版本相同,则忽略检测cpp版本.可以直接打空白增量包
        if (!$sameCppVersion) {
            $ClientPackParams['--ignoreCheckCppVersion'] = true;
            //并上传7牛
            $ClientPackParams['--autoUploadPackages'] = true;
        }
        $ClientPackParams['--maxPackCount'] = 2;
        $returnCode = $ClientPack->run(new ArrayInput($ClientPackParams),
            $output);
        if ($returnCode != 0) {
            return $returnCode;
        }
        return 0;

    }

    /**
     * 创建IPA
     * @param $codeSourcePath
     * @param $platformId
     * @param OutputInterface $output
     * @return int
     */
    private function createIpa($codeSourcePath, $platformId, OutputInterface $output)
    {
        if (!isset($this->PLATFORM_INFO[$platformId])) {
            $output->writeln("<error>渠道信息错误</error>");
            return 1;
        }
        $platformInfo = $this->PLATFORM_INFO[$platformId];

        //修改配置文件
        $projectPath = $codeSourcePath;


        $fs = new Filesystem();
        if (!$fs->exists($projectPath)) {
            $output->writeln('<error>.客户端路径不存在:' . $projectPath . '</error>');
            return 1;
        }
        $isoProjectPath = $projectPath . DIRECTORY_SEPARATOR . 'projects/YYGame/proj.ios/';
        if (!$fs->exists($isoProjectPath)) {
            $output->writeln('<error>.IOS工程路径不存在:' . $isoProjectPath . '</error>');
            return 2;
        }


        $IOSScheme = $platformInfo[self::PLATFORM_KEY_SCHEME];

        $output->write('编译IOS客户端...');

        $processBuilder = new ProcessBuilder();
        $processBuilder->setWorkingDirectory($isoProjectPath);
        $processBuilder->setPrefix('xcodebuild');
        $processBuilder->setArguments(
            [
                '-scheme',
                $IOSScheme,
                'clean',
                '-target',
                'YYGame',
                'archive',
                '-archivePath',
                $this->getBuildDirectory() . "YYGame.xcarchive",
            ]);
        $process = $processBuilder->getProcess();
        $process->setTimeout(null);

//        var_dump($process->getCommandLine());
        $returnCode = $process->run(function ($type, $buffer) use ($output) {
            $output->writeln($buffer);
        });
        if ($this->isVerboseDebug()) {
            var_dump($process->getOutput());
        }
        if ($returnCode) {
            var_dump($process->getErrorOutput());

            $output->writeln("<error>编译失败</error>");
            return 5;
        }

//        return;

        $output->writeln('编译IOS客户端完成');
        $output->write('开始打包ipa...');
        $ipaFileName = $this->getIpaPath() . 'YYGame_(' . $platformInfo[self::PLATFORM_KEY_CHANNEL_NAME] . ')_('
            . date('Ymd-His') . ').ipa';
        $processBuilder = new ProcessBuilder();
        $processBuilder->setWorkingDirectory($isoProjectPath);
        $processBuilder->setPrefix('xcodebuild');
        $processBuilder->setArguments(
            [
                '-exportArchive',
                '-exportFormat',
                'ipa',
                '-archivePath',
                $this->getBuildDirectory() . "YYGame.xcarchive",
                '-exportPath',
                $ipaFileName,
                '-exportProvisioningProfile',
                $platformInfo[self::ProvisioningProfile]
            ]);
        $process = $processBuilder->getProcess();
        $process->setTimeout(null);
        $returnCode = $process->run();

        if ($this->isVerboseDebug()) {
            var_dump($process->getOutput());
            var_dump($process->getErrorOutput());
        }

        if ($returnCode) {
            var_dump($process->getErrorOutput());
            $output->writeln("<error>开始打包ipa失败</error>");
            return 5;
        }


        $output->writeln([
            'Ipa路径:',
            $ipaFileName,

        ]);

        $luaVersion = $this->getLuaCodeVersion();

        $cppVersion = $this->getCppVersion();

        $channelName = $platformInfo[self::PLATFORM_KEY_CHANNEL_NAME];
        $ipaSVNFileName = "YYGame($channelName)_lua($luaVersion)_cpp($cppVersion).ipa";

        $output->writeln([
            "SVN:",
            $ipaSVNFileName
        ]);


        $output->writeln("<info>打包完成</info>");

        return 0;
    }


    /**
     * @return string
     */
    private function getBuildSourceDirectory()
    {
        return utils::getTempDirectoryPath() . 'ios_build_source' . DIRECTORY_SEPARATOR;
    }

    private function getBuildDirectory()
    {
        return utils::getTempDirectoryPath() . 'app_build' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    private function getIpaPath()
    {
        return $this->getBuildDirectory() . "ipa" . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取将要打包的版本号
     * @param boolean|true $fromSvn 是否从SVN获取
     * @return string
     */
    private function getLuaCodeVersion($fromSvn = true)
    {
        /**
         * app版本信息
         */
        $contents = "";

        if ($fromSvn) {
            $svnPath = "svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YY/appversion.lua";
            $process = utils::createSvnProcess([
                'cat',
                $svnPath,
            ]);
            $process->run();
            $contents = $process->getOutput();
        } else {

            $localFilePath = $this->getBuildSourceDirectory() . "projects/YYGame/Resources/YY/appversion.lua";
            $contents = file_get_contents($localFilePath);
        }


        $lines = explode("\n", $contents);
        foreach ($lines as $line) {
            if (util::starts_with($line, 'APP_VERSION = ')) {
                $Version = explode(" ", $line)[2];
                $Version = trim($Version);
                $Version = trim($Version, "\"");
                return $Version;
            }
        }
        return "";

    }

    /**
     * 获取Cpp版本号
     * @param boolean|true $fromSvn 是否从SVN获取
     * @return string
     */
    private function getCppVersion($fromSvn = true)
    {

        $contents = "";

        if ($fromSvn) {
            $svnPath = "svn://192.168.1.2/project/code/clientcocos/scripting/lua/cocos2dx_support/LuaYYEngine/YYConstPlatform.h";
            $process = utils::createSvnProcess([
                'cat',
                $svnPath
            ]);
            $process->run();
            $contents = $process->getOutput();
        } else {
            $localFilePath = $this->getBuildSourceDirectory() . "scripting/lua/cocos2dx_support/LuaYYEngine/YYConstPlatform.h";
            $contents = file_get_contents($localFilePath);
        }


        $lines = explode("\n", $contents);
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

    /**
     * 备份Ipa到SVN
     * @param OutputInterface $output
     */
    private function backUpIpaToSvn(OutputInterface $output)
    {
        $cppVersion = $this->getCppVersion(false);

        $output->writeln([
            '开始备份到SVN'
        ]);

        $finder = new Finder();
        $ipaFiles = $finder->name("*.ipa")
            ->depth('<3')
            ->in($this->getIpaPath())
            ->files();

        $ipaSVNPath = "svn://192.168.1.2/cooking_packages/trunk/installPackages/" . $cppVersion . "/";

        $packageClientVersion = $this->getLuaCodeVersion(true);
        $cppVersion = $this->getCppVersion(true);
        $csvSvnRevision = $this->getCSVResourceRevision(true);


        foreach ($ipaFiles as $ipaFile) {
            /**
             * @var $ipaFile SplFileInfo
             */

            $ipaSvnFileName = $ipaFile->getBasename(".ipa") . "_lua($packageClientVersion)cpp($cppVersion)csv($csvSvnRevision).ipa";

            var_dump($ipaSvnFileName);
            $process = utils::createSvnProcess([
                'import',
                $ipaFile->getPathname(),
                $ipaSVNPath . $ipaSvnFileName,
                '-m',
                'commit ipa Package, FileName:' . $ipaSvnFileName
            ]);
            $process->run();

            $output->writeln([
                '<info>备份完成</info>',
                $ipaSVNPath . $ipaFile->getFilename()
            ]);
        }

    }

    /**
     * 获取csv资源版本号
     * @param boolean|true $fromSvn 是否从SVN获取
     * @return null
     */
    private function getCSVResourceRevision($fromSvn = true)
    {
        $contents = "";
        if ($fromSvn) {
            //lua包中资源的版本号
            $luaCsvSvnPath = "svn://192.168.1.2/project/code/clientcocos/projects/YYGame/Resources/YY/Game/json/csv_resources_setting.lua";
            $process = utils::createSvnProcess([
                'cat',
                $luaCsvSvnPath,
            ]);
            $process->run();

            if ($process->getExitCode()) {
                throw new exception("客户端CSV资源版本号无效");
            }

            $contents = $process->getOutput();
        } else {
            $localFilePath = $this->getBuildSourceDirectory() . "projects/YYGame/Resources/YY/Game/json/csv_resources_setting.lua";
            $contents = file_get_contents($localFilePath);
        }


        $luaCvsSvnContents = explode("\n", $contents);
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
     * tag客户端
     * @return string
     */
    private function tag()
    {
        $packageClientVersion = $this->getLuaCodeVersion();
        $cppVersion = $this->getCppVersion();
        $csvSvnRevision = $this->getCSVResourceRevision();

        $tagSvnPath = $this->tagDestPath . "lua($packageClientVersion)cpp($cppVersion)csv($csvSvnRevision)";
        //tag客户端
        svn::tag($this->tagSourcePath,
            $tagSvnPath,
            "commit ipa Package clientVersion:" . $packageClientVersion);

        return $tagSvnPath;

    }

}