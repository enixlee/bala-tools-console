<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 */

namespace miniPayCenter\RpcCodeTemplates\RpcBridge\<?php echo $generateClass->getNameSpace()?>;
<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters\LogicBridgeTemplatesWriter($generateClass)?>

use Pluto\Contracts\RPC\RpcRemoteCaller;
use Pluto\Foundation\RPC\RPCCommandResult;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getClassName() . "\n"?>
<?php
if($generateClass->isDeprecated())
{
    echo " * @deprecated\n";
}
?>
 * @package miniPayCenter\RpcCodeTemplates\RpcBridge\<?php echo $generateClass->getNameSpace(). "\n"?>
 */
class RpcBridge<?php echo $generateClass->getClassName()."\n" ?>
{
    /**
     * <?php echo $generateClass->getDescription() . "\n"?>
<?php
if($generateClass->isDeprecated())
{
    echo "     * @deprecated\n";
}
?>
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return RPCCommandResult
     */
    public static function <?php echo $generateClass->getFunctionName()?>(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>)
    {
<?php
if($generateClass->isDeprecated())
{
    echo "        logicErrorInterfaceDeprecated();\n";
}
?>

<?php $declare = $generateClass->getParameterCheck();echo join("\n",$declare); ?>

        /**
        * @var $rpcCaller RpcRemoteCaller
        */
        $rpcCaller = app('RpcRemoteCaller');
        return $rpcCaller->call('<?php echo $generateClass->getRouteUrl() ?>',
            [
<?php $declare = $generateClass->getParameterAsArrayWithParameterVar();echo join(",\n",$declare)."\n"; ?>
            ]
        );
    }

}