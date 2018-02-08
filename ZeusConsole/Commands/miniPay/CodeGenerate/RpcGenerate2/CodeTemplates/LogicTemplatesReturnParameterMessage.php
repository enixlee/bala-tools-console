<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 * toolsVersion:<?php print getConfig('version')."\n"?>
 */

namespace <?php echo $generateClass->getNameSpace()?>\RPC;

use Pluto\Foundation\Serializer\ObjectSerializerTrait;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getRpcReturnParametersClassName() . "\n"?>
 * @package <?php echo $generateClass->getNameSpace()?>\RPC
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