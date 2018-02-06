#!/bin/sh
cd /Users/lijiang/Documents/work/paymini/minipay-tools-Console/bin/

php zeus.phar codeGenerate:rpcGenerate --exportPath=/Users/lijiang/Documents/work/paymini/SDK-Manage\ \(trunk\)/src/App/CodeTemplates/Logics --exportTestsPath=/Users/lijiang/Documents/work/paymini/SDK-Manage\ \(trunk\)/Tests/App/Route/CodeTemplates --exportBridgePath=/Users/lijiang/Documents/work/paymini/SDK-Manage\ \(trunk\)/src/App/CodeTemplates/RpcBridge

echo "\033[32mexport RPC finished:\033[0m""\033[33m codeGenerate:rpcGenerate \033[0m"