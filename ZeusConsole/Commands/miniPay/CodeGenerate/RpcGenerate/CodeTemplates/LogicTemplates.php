<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 */

namespace App\CodeTemplates\Logics;

use Pluto\Foundation\RPC\RPCCommandResult;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getClassName() . "\n"?>
 * @package App\CodeTemplates\Logics
 */
trait <?php echo $generateClass->getClassName()."\n" ?>
{
    /**
     * @var string
     */
    public static $<?php echo $generateClass->getClassName()?>RpcUrl = '<?php echo $generateClass->getRouteUrl() ?>';
    /**
     * @var string
     */
    public static $<?php echo $generateClass->getClassName()?>RpcFunctionName = '<?php echo $generateClass->getFunctionName()?>';

    /**
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return RPCCommandResult
     */
    public function <?php echo $generateClass->getFunctionName()?>(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>)
    {

<?php $declare = $generateClass->getParameterCheck();echo join("\n",$declare); ?>

        return $this->Do<?php echo $generateClass->getFunctionName()?>(<?php $declare = $generateClass->getParameterTransfer();echo join(", ",$declare);?>);
    }

    /**
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return RPCCommandResult
     */
    abstract protected function Do<?php echo $generateClass->getFunctionName()?>(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare);?>
);

}