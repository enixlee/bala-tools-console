<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 * toolsVersion:<?php print getConfig('version')."\n"?>
 */

namespace <?php echo $generateClass->getNameSpace()?>\RPC;

use Pluto\Foundation\Serializer\ObjectSerializerTrait;
use bala\codeTemplate\Supports\CodeTemplate\ReturnParameterSerializerTrait;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getRpcReturnParametersClassName() . "\n"?>
 * @package <?php echo $generateClass->getNameSpace()?>\RPC
 */
class <?php echo $generateClass->getRpcReturnParametersClassName()."\n" ?>
{
    use ObjectSerializerTrait;
    use ReturnParameterSerializerTrait;
<?php foreach ($rpcOutputParameters as $param) : ?>
    <?php echo $view->render('LogicTemplatesReturnParameterSetterAndGetter.php',['param'=>$param]) ?>
    <?php echo "\n" ?>
<?php endforeach ?>
<?php echo "\n" ?>
}