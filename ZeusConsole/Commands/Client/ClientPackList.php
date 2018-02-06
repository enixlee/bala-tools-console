<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/4/22
 * Time: 下午2:29
 */

namespace ZeusConsole\Commands\Client;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\LogicException;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\svn;
use ZeusConsole\Utils\utils;

class ClientPackList extends CommandBase
{
    private $svnPackPath = "svn://192.168.1.2/cooking_packages/trunk/clientUpgradePackageInfo/packagelist.json";

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("client:packList");
        $this->setDescription("打包列表管理");


        $this->addOption("list", null, InputOption::VALUE_NONE, "列出目前已经打包列表");
        $this->addOption("remove", null, InputOption::VALUE_OPTIONAL,
            "删除无用的tag.客户端的lua版本号");


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
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $isList = $input->getOption("list");

        $contents = svn::cat($this->svnPackPath);
        if ($isList) {

            var_dump($contents);
            return;
        }

        $remove = $input->getOption("remove");
        if (!is_null($remove)) {
            $packages = utils::getSerializer()->deserialize($contents,
                '\ZeusConsole\Commands\Client\ClientPackData[]', "json");

            $dataChange = false;
            $changePackages = [];
            foreach ($packages as $key => $package) {
                /**
                 * @var $package ClientPackData
                 */
                if ($package->getClientversion() === $remove) {
                    $dataChange = true;
                } else {
                    $changePackages[] = $package;
                }
            }

            if ($dataChange) {
                $this->savePackages($changePackages);

                //删除无用的tag
                $this->removeUsedTag($remove);
                $output->writeln("删除完成!!" . $remove);
            } else {

                $output->writeln("没有找到要删除的版本!" . $remove);
            }

        }

        return;
    }


    private function getPackagePath()
    {
        return utils::getTempDirectoryPath() . 'clientPackages' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param ClientPackData[] $packages
     */
    private function savePackages(array $packages)
    {
        $packageContents = utils::getSerializer()->serialize($packages
            , 'json', ['json_encode_options' => JSON_PRETTY_PRINT]);

        $fs = new Filesystem();
        $fs->remove($this->getPackagePath());
        $fs->mkdir($this->getPackagePath());


        $process = svn::createSvnProcess(
            [
                'checkout',
                pathinfo($this->svnPackPath, PATHINFO_DIRNAME),
                $this->getPackagePath()
            ]
        );
        $process->run();
        $process = utils::createSvnProcess([
            'up',
        ]);
        $process->setWorkingDirectory($this->getPackagePath());
        $process->run();

        $fs->dumpFile($this->getPackagePath() . "packagelist.json",
            $packageContents);

        $process = utils::createSvnProcess([
            'commit',
            pathinfo($this->svnPackPath, PATHINFO_BASENAME),
            '-m',
            "remove used package~~"
        ]);
        $process->setWorkingDirectory($this->getPackagePath());
        $process->run();

    }

    /**
     * @param $clientVersion
     */
    private function removeUsedTag($clientVersion)
    {
        //svn根目录
        $svnRootPath = "svn://192.168.1.2/cooking_packages/trunk/";
        $packageUpgradeSvnPath = $svnRootPath . "clientUpgradePackages/" . $clientVersion;

        $process = utils::createSvnProcess(
            [
                'ls',
                $packageUpgradeSvnPath
            ]
        );
        $process->run();
        if ($process->getExitCode() === 0) {
            $process = utils::createSvnProcess([
                'delete',
                $packageUpgradeSvnPath,
                '-m',
                "remove Unused Packages: lua(" . $clientVersion . ")"
            ]);
            $process->run();
        }
    }


}