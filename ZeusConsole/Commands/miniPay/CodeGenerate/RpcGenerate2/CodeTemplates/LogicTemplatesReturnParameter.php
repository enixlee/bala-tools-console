<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 * toolsVersion:<?php print getConfig('version')."\n"?>
 */

namespace miniPayCenter\RpcCodeTemplates\RPC\<?php echo $generateClass->getNameSpace()  ?>;

use Pluto\Foundation\Serializer\ObjectSerializerTrait;
use miniPayCenter\Supports\CodeTemplate\ReturnParameterSerializerTrait;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getRpcReturnParametersClassName() . "\n"?>
 * @package miniPayCenter\RpcCodeTemplates\RPC\<?php echo $generateClass->getNameSpace() . "\n"?>
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