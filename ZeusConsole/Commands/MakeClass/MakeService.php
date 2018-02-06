<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/19
 * Time: 上午9:52
 */

namespace ZeusConsole\Commands\MakeClass;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use ZeusConsole\Commands\CommandBase;
use ZeusConsole\Utils\utils;

class MakeService extends CommandBase
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('makeClass:Service')
            ->setDescription('创建Service类');

        $this->addArgument('className', InputArgument::REQUIRED, 'Service类名');


        //判断当前的运行环境
        $this->addArgument('dest', InputArgument::OPTIONAL, '输出文件位置', utils::judgePath());

        $this->addOption('Not-Need-Login', null, InputOption::VALUE_NONE, '是否需要登录调用,不赋值则是需要验证');
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
        $className = $input->getArgument('className');

        $fs = new Filesystem();
        $templateFilePath = __DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates" . DIRECTORY_SEPARATOR . 'Service.rtf';

        $templateString = file_get_contents($templateFilePath);
        $outString = translator()->trans($templateString,
            [
                '{{ className }}' => $className,
                '{{ needLogin }}' => ($input->getOption('Not-Need-Login') ? 'false' : 'true')
            ]);

        $outFilePath = $input->getArgument('dest') . DIRECTORY_SEPARATOR . 'service_' . $className . '.php';

        var_dump($outString);

        $loader = new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . "CodeTemplates/%name%");

        $templating = new PhpEngine(new TemplateNameParser(), $loader);

        var_dump($templating->render("Service.php", [
            'className' => $className,
            'needLogin' => ($input->getOption('Not-Need-Login') ? 'false' : 'true')
        ]));
//        $fs->dumpFile($outFilePath, $outString);

        $output->writeln('<info>创建成功: ' . $outFilePath . '</info>');
    }


}