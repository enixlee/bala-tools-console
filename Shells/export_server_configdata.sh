#!/bin/sh
rm -rf ~/Documents/Object/cooking_server/trunk/webgame/include/configdata/*.*
php ../zeus csv:export svn://192.168.1.2/cooking_docs/trunk/999-数据表/csv表格 ~/Documents/Object/cooking_server/trunk/webgame/include/configdata -s --export-format php --delete