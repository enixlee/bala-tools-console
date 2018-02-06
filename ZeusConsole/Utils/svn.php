<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/4/20
 * Time: 下午4:32
 */

namespace ZeusConsole\Utils;


use Symfony\Component\Process\ProcessBuilder;
use utilphp\util;

class svn
{
    /**
     * 创建SVN处理器
     * @param array $arguments
     * @param bool|true $appendDefaultUserNameOrPassword
     * @return \Symfony\Component\Process\Process
     */
    static function createSvnProcess(Array $arguments, $appendDefaultUserNameOrPassword = true)
    {
        $processBuilder = new ProcessBuilder();
        $processBuilder->setPrefix('svn');
        $svnArguments = $arguments;
        if ($appendDefaultUserNameOrPassword) {
            $svnArguments [] = '--username';
            $svnArguments [] = getConfig('SVNUserName', 'empty');
            $svnArguments [] = '--password';
            $svnArguments [] = getConfig('SVNPassWord', 'empty');
            $svnArguments [] = '--no-auth-cache';
        }
        $processBuilder->setArguments($svnArguments);
        $process = $processBuilder->getProcess();
        $process->setTimeout(null);
        return $process;
    }

    /**
     * 获取指定svn路径的最新版本号
     * @param $svnPath
     * @return string
     */
    static function getSvnRevision($svnPath)
    {
        $process = self::createSvnProcess([
            'info',
            $svnPath
        ]);
        $process->run();

        //报错了
        if ($process->getExitCode()) {
            return "";
        }

        $lines = explode("\n", $process->getOutput());

//        var_dump($lines);
        /**
         * SVN总版本号
         */
        $Revision = "";
        /**
         * 路径中最后修改版本号
         */
        $LastChangeRevision = "";
        foreach ($lines as $line) {
            if (util::starts_with($line, 'Revision: ')) {
                $Revision = trim(explode(" ", $line)[1]);
            } elseif (util::starts_with($line, 'Last Changed Rev: ')) {
                $LastChangeRevision = substr($line, strlen('Last Changed Rev: '));
            }
            elseif (util::starts_with($line, '最后修改的版本: ')) {
                $LastChangeRevision = substr($line, strlen('最后修改的版本: '));
            }
        }

        return empty($LastChangeRevision) ? $Revision : $LastChangeRevision;

    }

    /**
     * tag
     * @param string $fromPath 原始路径
     * @param string $toPath 目标路径
     * @param string $message 注释信息
     * @return \Symfony\Component\Process\Process
     */
    static function tag($fromPath, $toPath, $message)
    {
        $process = self::createSvnProcess([
            'copy',
            $fromPath,
            $toPath,
            '-m',
            $message,
        ]);
        $process->run();
        return $process;
    }


    /**
     * @param $svnPath
     * @param int $revision 指定版本号 0为最新
     * @return string
     */
    static function cat($svnPath, $revision = 0)
    {
        $revisionString = $revision === 0 ? "HEAD" : $revision;
        $process = self::createSvnProcess([
            'cat',
            $svnPath,
            "-r",
            $revisionString
        ]);
        $process->run();
        return $process->getOutput();
    }
}