#!/bin/sh
php ../zeus webGameCodeTemplate:MakeDataBase \
--templatePath="/Users/zhipeng/Documents/Object/cooking_server/trunk/webgame/include/apps/payverify/DBTemplates" \
--exportPath="/Users/zhipeng/Documents/Object/cooking_server/trunk/webgame/include/apps/payverify/dbs/templates" \
--superNameSpace="apps\\payverify\\dbs\\templates" \
--parentClassPlayerDB="apps\\payverify\\dbs\\dbs_base" \
--parentClassGlobalDB="apps\\payverify\\dbs\\dbs_base" \
--parentClassDataCell="apps\\payverify\\dbs\\dbs_basedatacell"