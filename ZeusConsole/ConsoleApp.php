<?php

namespace ZeusConsole;

use ZeusConsole\Application\ApplicationBase;
use ZeusConsole\Commands\Client\ClientBuildIOSFromSvn;
use ZeusConsole\Commands\Client\ClientPack;
use ZeusConsole\Commands\Client\ClientPackageRemoveUnused;
use ZeusConsole\Commands\Client\ClientPackList;
use ZeusConsole\Commands\CSV\CheckCsv;
use ZeusConsole\Commands\CSV\CheckCsvItemIcon;
use ZeusConsole\Commands\CSV\ExportCsv;
use ZeusConsole\Commands\CSV\ExportLuaFont;
use ZeusConsole\Commands\GreetCommand;
use ZeusConsole\Commands\MakeClass\MakeDataBaseYAML;
use ZeusConsole\Commands\MakeClass\MakeDataCell;
use ZeusConsole\Commands\MakeClass\MakeDbsPlayerCell;
use ZeusConsole\Commands\MakeClass\MakeService;
use ZeusConsole\Commands\RsyncCommands\rsyncGameCSVResource;
use ZeusConsole\Commands\RsyncCommands\rsyncGameMapResource;
use ZeusConsole\Commands\RsyncCommands\rsyncServerConfigByClientTag;
use ZeusConsole\Commands\RsyncCommands\ServerCSVResourcePublish;
use ZeusConsole\Commands\RsyncCommands\ServerPayVerifyPublish;
use ZeusConsole\Commands\RsyncCommands\ServerPublic;
use ZeusConsole\Commands\System\dumpConfig;
use ZeusConsole\Commands\System\showConfig;

class ConsoleApp extends ApplicationBase
{


    protected function getDefaultCommands()
    {
        $Commands = parent::getDefaultCommands();
        $Commands[] = new GreetCommand ();

        $Commands[] = new rsyncGameCSVResource ();
        $Commands[] = new rsyncGameMapResource();
        $Commands[] = new rsyncServerConfigByClientTag();

        $Commands[] = new ExportCsv ();
        $Commands[] = new CheckCsv ();
        $Commands[] = new MakeService();
        $Commands[] = new CheckCsvItemIcon();

        $Commands[] = new MakeDbsPlayerCell();
        $Commands[] = new MakeDataCell();
        $Commands[] = new MakeDataBaseYAML();

        $Commands[] = new ClientPack();
//        $Commands[] = new ClientBuildIOS();
        $Commands[] = new ClientBuildIOSFromSvn();
        $Commands[] = new ClientPackList();


        $Commands[] = new ServerPublic();
        $Commands[] = new ServerPayVerifyPublish();
        $Commands[] = new ServerCSVResourcePublish();

        $Commands[] = new ExportLuaFont();
        $Commands[] = new ClientPackageRemoveUnused();


        $Commands[] = new showConfig();
        $Commands[] = new dumpConfig();
        return $Commands;
    }


}
