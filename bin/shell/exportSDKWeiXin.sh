#!/bin/sh
cd /Users/lijiang/Documents/work/paymini/minipay-tools-Console/bin

php zeus.phar codeGenerate:vueRpcExport --useFullPath=true --exportVueRpcPath=/Users/lijiang/Documents/work/paymini-web-work/web-weixin-merchant-manage/src/api/Template --templateVueRpcPath=/Users/lijiang/Documents/work/paymini/minipay-code-template/CodeTemplates/Rpc/SDKManage@//Users/lijiang/Documents/work/paymini/minipay-code-template/CodeTemplates/Rpc/CustomDevelopment/;

echo "\033[32mexport RPC finished:\033[0m""\033[33m codeGenerate:vueRpcExport \033[0m"