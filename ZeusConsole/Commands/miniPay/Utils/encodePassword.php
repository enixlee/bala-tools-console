<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 2017/4/5
 * Time: 下午2:52
 */

namespace ZeusConsole\Commands\miniPay\Utils;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZeusConsole\Commands\CommandBase;

class encodePassword extends CommandBase
{
    protected function configure()
    {
        $this->setName("utils:encodePassword")->setDescription('计算密码');
        $this->addArgument('password', InputArgument::REQUIRED, "需要加密的原始密码");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $originPassword = $input->getArgument('password');

        $output->writeln("原始密码:<info>$originPassword</info>");
        $output->writeln("原始密码MD5:<info>" . md5($originPassword) . "</info>");
        $md5Password = HexMd5::md5_16($originPassword);
        $output->writeln("原始密码MD5(16):<info>$md5Password</info>");

        //会员登录密码
        $encodePassword = password_hash(md5($originPassword), PASSWORD_BCRYPT);
        $output->writeln("会员登录密码[LOGIN_PASSWORD_ENCODE]:<info>$encodePassword</info>");
        //预付卡支付密码
        $bigintPassword = HexMd5::hex16ToBigint($md5Password);
        $output->writeln("预付卡:支付密码[TRANSACTION_PWD]:<info>$bigintPassword</info>");
        $encodePassword = password_hash($bigintPassword, PASSWORD_BCRYPT);
        $output->writeln("预付卡加密:支付密码[TRANSACTION_PWD_ENCODE]:<info>$encodePassword</info>");

        $output->writeln("会员支付密码:支付密码[PAY_PASSWORD]:<info>$bigintPassword</info>");
        $output->writeln("会员支付密码加密:支付密码[PAY_PASSWORD_ENCODE]:<info>$encodePassword</info>");

        return 0;
    }


}