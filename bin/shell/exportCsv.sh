#!/bin/bash

#导出客户端csv
/usr/bin/php /Users/enixlee/Desktop/project/code/client/project/code/servertools/gameconsole/zeus.phar csv:export svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格 /Users/enixlee/Desktop/project/code/client/project/code/clientcocos/projects/YYGame/Resources/YY/Game/json -c --export-format lua 

#上传服务器配置
/usr/bin/php /Users/enixlee/Desktop/project/code/client/project/code/servertools/gameconsole/zeus.phar rsync:gameconfig