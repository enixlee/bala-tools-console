<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 */

namespace App\CodeTemplates\RpcBridge;

use Pluto\Contracts\RPC\RpcRemoteCaller;
use Pluto\Foundation\RPC\RPCCommandResult;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getClassName() . "\n"?>
 * @package App\CodeTemplates\Rpc\RpcBridge
 */
class RpcBridge<?php echo $generateClass->getClassName()."\n" ?>
{
    /**
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return RPCCommandResult
     */
    public static function <?php echo $generateClass->getFunctionName()?>(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>)
    {

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