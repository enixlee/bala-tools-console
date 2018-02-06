#!/bin/sh
cd /Users/lijiang/Documents/work/paymini-git/minipay-code-template-client/

php vendor/bin/zeus codeGenerate:vueRpcExport --templateVueRpcPath=/Users/lijiang/Documents/work/paymini/minipay-code-template/CodeTemplates/Rpc/CustomDevelopment/PublicMerchant@/Users/lijiang/Documents/work/paymini-git/minipay-code-template-client/CodeTemplates/Rpc/Client --exportVueRpcPath=/Users/lijiang/Documents/work/paymini-web-work/web-weixin-merchant/src/api/Template;

echo "\033[32mexport RPC finished:\033[0m""\033[33m codeGenerate:vueRpcExport \033[0m"
