#!/bin/sh
cd /Users/lijiang/Documents/work/paymini/minipay-tools-Console/bin

php zeus.phar codeGenerate:vueRpcExport --useFullPath=true --exportVueRpcPath=/Users/lijiang/Documents/work/paymini-web-work/web-developer-platform/src/api/Template --templateVueRpcPath=/Users/lijiang/Documents/work/paymini/minipay-code-template/CodeTemplates/Rpc/SDKDeveloper;

echo "\033[32mexport RPC finished:\033[0m""\033[33m codeGenerate:vueRpcExport \033[0m"