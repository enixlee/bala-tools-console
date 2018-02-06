<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 * toolsVersion:<?php print getConfig('version')."\n"?>
 */

namespace miniPayCenter\RpcCodeTemplates\RPC\<?php printf("%s",$generateClass->getNameSpace())  ?>;

use Pluto\Foundation\Serializer\ObjectSerializerTrait;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getRpcReturnParametersClassName() . "\n"?>
 * @package miniPayCenter\RpcCodeTemplates\RPC\<?php echo $generateClass->getNameSpace() . "\n"?>
 */
class <?php printf($rpcOutputParameter->getMessageClassName()."\n") ?>
{
    use ObjectSerializerTrait;
<?php foreach ($rpcOutputParameter->getMessageData() as $param) : ?>
    <?php echo $view->render('LogicTemplatesReturnParameterSetterAndGetter.php',['param'=>$param]) ?>
    <?php echo "\n" ?>
<?php endforeach ?>
<?php echo "\n" ?>
}