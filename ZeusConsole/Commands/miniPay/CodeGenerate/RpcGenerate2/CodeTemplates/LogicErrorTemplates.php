<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 * toolsVersion:<?php print getConfig('version')."\n"?>
 */

namespace miniPayCenter\RpcCodeTemplates\Errors\<?php echo $generateClass->getNameSpace()?>;

use miniPayCenter\Supports\Errors\ErrorBase;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getClassName() . "\n"?>
 * @package miniPayCenter\RpcCodeTemplates\Errors\<?php echo $generateClass->getNameSpace() ."\n"?>
 */
interface Error<?php echo $generateClass->getClassName()." extends ErrorBase\n" ?>
{
<?php $errorCodes = $generateClass->getErrorCodes();
foreach ($errorCodes as $errorCode)
{
    echo "    /**\n";
    echo "     * " . $errorCode->getComment()."\n";
    echo "     */\n";
    echo "    const ".$errorCode->getName() . " = \"" . $errorCode->getCode()."\";\n";

}
?>
}