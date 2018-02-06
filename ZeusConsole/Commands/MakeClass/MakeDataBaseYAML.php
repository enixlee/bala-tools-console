<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/12/10
 * Time: 下午7:43
 */

namespace ZeusConsole\Commands\MakeClass;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

class MakeDataBaseYAML extends CommandBase
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("webGameCodeTemplate:MakeDataBaseYAML")
            ->setDescription("创建服务器数据库模板类 YAML");

        $this->addArgument("className", InputArgument::REQUIRED, "数据完整类名,例如examples.role");

        $this->addOption("DBType", null, InputOption::VALUE_OPTIONAL,
            "数据模板类型,0:PlayerDB 1:globalDB 2:dateCell", 0);

        $this->addOption("parent", '-p', InputOption::VALUE_OPTIONAL, "继承的父类,完整类名 例如 examples.role");

        $this->addOption('exportPath', null, InputOption::VALUE_OPTIONAL, "类模板导出路径",
            $this->getDBSTemplatePath());
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
        $exportPath = $input->getOption('exportPath');

        $fullClassName = $input->getArgument("className");
        $DBType = $input->getOption("DBType");
        $parent = $input->getOption("parent");
        if (empty($parent)) {
            $parent = "# parent: ";
        } else {
            $parent = "parent: " . $parent;
        }

        $arr = explode(".", $fullClassName);
        $className = end($arr);

        //命名空间名称
        $namespace = "";
        //数据表名称
        $tableName = $className;
        if ($className !== $fullClassName) {
            $fullClassNameArray = explode(".", $fullClassName);
            array_pop($fullClassNameArray);

            $namespace = join(DIRECTORY_SEPARATOR, $fullClassNameArray);
            $exportPath .= DIRECTORY_SEPARATOR . $namespace;
            //转换数据表名称
            $tableName = join("_", explode(".", $fullClassName));
        }
        $exportPath .= DIRECTORY_SEPARATOR . $className . ".yaml";


        var_dump($exportPath);

        $fs = new Filesystem();
        if ($fs->exists($exportPath)) {
            $helper = $this->getHelper('question');
            if ($helper instanceof QuestionHelper) {
                $question = new ConfirmationQuestion("文件已经存在,是否覆盖[$exportPath]?(y/n):", false);
                $bundle = $helper->ask($input, $output, $question);
                if (!$bundle) {
                    $output->writeln('<error>放弃</error>');
                    return 1;
                }
            }
        }

        $templateString = $this->getTemplateString();
        $classString = translator()->trans($templateString,
            [
                "{{ className }}" => $className,
                "{{ DBType }}" => $this->getDBTypeString($DBType),
                "{{ tableName }}" => $tableName,
                "{{ parent }}" => $parent
            ]
        );

//        var_dump($classString);
        $fs->dumpFile($exportPath, $classString);

        $output->writeln("<info>生成成功:$exportPath</info>");

        return 0;
    }

    private function getTemplateString()
    {
        $templateFilePath = __DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates" . DIRECTORY_SEPARATOR . 'DataBaseYAML.rtf';
        $templateString = file_get_contents($templateFilePath);
        return $templateString;
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

    private function getDBTypeString($DBType)
    {
        $DBType = intval($DBType);
        $DBTypeString = "";
        switch ($DBType) {
            case 0:
                $DBTypeString = "playerDB";
                break;
            case 1:
                $DBTypeString = "globalDB";
                break;
            case 2:
                $DBTypeString = "dataCell";
                break;

        }
        return $DBTypeString;
    }


}