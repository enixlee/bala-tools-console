<?php
/**
 * Created by PhpStorm.
 * User: peng
 * Date: 15-10-28
 * Time: 下午3:59
 */

namespace ZeusConsole\Commands\CSV;

use utilphp\util;
use ZeusConsole\Commands\CommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZeusConsole\Utils\utils;

/**
 * Class ExportLuaFont
 * @package ZeusConsole\Commands\CSV
 */
class ExportLuaFont extends CommandBase
{
    /**
     * Configures the current command.
     */

    protected function configure(){

        $this->setName('csv:exportLuaFont');
        $this->setDescription('.fnt格式字体导成.lua格式');
        $this->addArgument('svn-path', InputArgument::REQUIRED, 'font数据源路径,可以使用svn路径');
        $this->addArgument('export-path',InputArgument::REQUIRED, 'lua数据导出到的位置');
    }

    /**
     * 执行导出命令方法
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input,OutputInterface $output){

        $SVNPath = $input->getArgument('svn-path');
        
        $fs = new Filesystem();

        $localFontSVNPath = $this->getExportLuaFontPath();

        $fs->remove($localFontSVNPath);
        $fs->mkdir($localFontSVNPath);

        if(util::starts_with($SVNPath,'svn://')){       //验证svn地址
            $process = utils::createSvnProcess([
                'export',
                $SVNPath,
                $localFontSVNPath,
                '--force'
            ]);
            $process->run();
            if($this->verboseDebug){
                $output->writeln('<info>' . $process->getCommandLine() . '</info>');
                $output->writeln('<error>' . $process->getErrorOutput() . '</error>');
                $output->writeln('<info>' . $process->getOutput() . '</info>');
            }

            //完成后修改csv路径
            $SVNPath = $localFontSVNPath;
        }else{
            if (!$fs->exists($SVNPath)) {
                $output->writeln('<error>SVN:路径错误:' . $SVNPath . '</error>');
                return 1;
            }
        }
        //判断导出路径
        $exportPath = $input->getArgument('export-path');
        if (!$fs->exists($exportPath)) {
            $output->writeln('<error>CSV:导出路径错误:' . $exportPath . '</error>');
            return 1;
        }

        $svnFiles = utils::getFiles($SVNPath, ['fnt']); //获取后缀为.fnt的文件
        $exportCount = 0;
        foreach($svnFiles as $svnFile){
            $outArrs = [];
            $fileContent =file_get_contents($svnFile); //读取文本内容
            $fileLine =explode("\n",$fileContent);
            $lines = array_filter($fileLine); //过滤空值

            for($i = 4;$i<count($lines);$i++){ //取出第四行以后的值
                $line = $lines[$i];
                $line_seps = explode(' ',$line);

                $outArr = [];

                foreach($line_seps as $line_sep){   //取出需要的值

                    $line_sep = trim($line_sep);

                    if (empty($line_sep)) {
                        continue;
                    }

                    if (util::starts_with($line_sep, 'id')) {
                        $filedName = explode('=', $line_sep);
                        $outArr['id'] = $filedName[1];

                    }elseif (util::starts_with($line_sep, 'width')) {

                        $filedName = explode('=', $line_sep);
                        $outArr['width'] = $filedName[1];

                    }elseif (util::starts_with($line_sep, 'height')) {
                        $filedName = explode('=', $line_sep);
                        $outArr['height'] = $filedName[1];

                    }elseif (util::starts_with($line_sep, 'xadvance')) {

                        $filedName = explode('=', $line_sep);
                        $outArr['xadvance'] = $filedName[1];
                    }
                }
                $outArrs[] = $outArr;
            }


            $exportFileNameWithTxt= basename($svnFile,'.fnt');
            $exportFileName= basename($exportFileNameWithTxt,'.txt');
            $exportCodeString = $this->exportFont($exportFileName,$outArrs);
            if(!empty($exportCodeString)){
                $codePath = $exportPath . DIRECTORY_SEPARATOR . "font_config_" . $exportFileName . ".lua" ;
                $output->writeln('<info>正在导出:' . $codePath . '</info>');
                $fs->dumpFile($codePath, $exportCodeString);
                $exportCount++;
            }else{
                $codePath = $exportPath . DIRECTORY_SEPARATOR . "font_config_" . $exportFileName . ".lua" ;
                $output->writeln('<error>导出文件失败:' . $codePath . '</error>');
                return 1;
            }
        }
        $output->writeln('<info>导出完成,共导出:' . $exportCount . '个文件</info>');
        return 0;
    }


    /**
     * 导成lua格式
     * @param string $fileName
     * @param array $datas
     * @return string
     */
    protected function exportFont($fileName,$datas){

        $luaCode = 'local ' . $fileName . " = {\n";
        foreach($datas as $data){
            $luaCode .= "[" . $data['id'] . "]";
            $luaCode .= " = ";
            $luaCode .= "{ ";
            $luaCode .= '["width"] = '. $data['width'] . " , ";
            $luaCode .= '["height"] = ' . $data['height'] ." , ";
            $luaCode .= '["xadvance"] = ' . $data['xadvance'] . " ";
            $luaCode .= "},\n";
        }
        $luaCode .= "}\nreturn " . $fileName;

        return $luaCode;
    }

    /**
     * 获取文件路径
     * @return string
     */
    private function getExportLuaFontPath(){
        $dirPath = utils::getTempDirectoryPath() . 'svnExport';
        return $dirPath;
    }
}