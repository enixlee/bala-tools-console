<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/4/5
 * Time: 下午3:54
 */

namespace ZeusConsole\Commands\miniPay\Utils;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZeusConsole\Commands\CommandBase;

class verifyEncodePassword extends CommandBase
{
    protected function configure()
    {
        $this->setName("utils:verifyEncodePassword")->setDescription('校验密码');
        $this->addArgument('password', InputArgument::REQUIRED, "原始密码");
        $this->addArgument('verifyPassword', InputArgument::REQUIRED, "密码密文");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $originPassword = $input->getArgument('password');
        $encodePassword = $input->getArgument('verifyPassword');

        $result = password_get_info($encodePassword);
        if ($result['algo'] == 0) {
            $output->writeln('<error>verifyPassword 格式错误!请注意密码中的$需要转义 \$</error>');
            return 1;
        }

        $output->writeln("原始密码:<info>$originPassword</info>");
        $output->writeln("hash密码:<info>$encodePassword</info>");
        //会员登录密码
        $verify = password_verify(md5($originPassword), $encodePassword);
        $verify = $verify ? "<info>通过</info>" : "<error>不通过</error>";
        $output->writeln("会员登录密码[LOGIN_PASSWORD_ENCODE]:$verify");


        //支付密码
        $verify = password_verify(HexMd5::hex16ToBigint(HexMd5::md5_16($originPassword)), $encodePassword);
        $verify = $verify ? "<info>通过</info>" : "<error>不通过</error>";
        $output->writeln("会员支付密码[PAY_PASSWORD]:$verify");

        return 0;
    }


}