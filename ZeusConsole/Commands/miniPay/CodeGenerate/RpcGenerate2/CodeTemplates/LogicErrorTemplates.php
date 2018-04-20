<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 */

namespace <?php echo $generateClass->getNameSpace()?>\Errors;

use bala\codeTemplate\Supports\Errors\ErrorBase;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getClassName() . "\n"?>
 * @package <?php echo $generateClass->getNameSpace()?>\Errors
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