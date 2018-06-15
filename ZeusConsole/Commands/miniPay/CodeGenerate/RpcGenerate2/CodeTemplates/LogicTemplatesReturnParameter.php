<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 */

namespace <?php echo $generateClass->getNameSpace()?>\RPC;

use bala\codeTemplate\Supports\CodeTemplate\ReturnParameterSerializerTrait;
use Pluto\Foundation\Serializer\YAMLObject\ReturnParameterObject;

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getRpcReturnParametersClassName() . "\n"?>
 * @package <?php echo $generateClass->getNameSpace()?>\RPC
 */
class <?php echo $generateClass->getRpcReturnParametersClassName()." extends ReturnParameterObject\n" ?>
{
    use ReturnParameterSerializerTrait;
<?php foreach ($rpcOutputParameters as $param) : ?>
    <?php echo $view->render('LogicTemplatesReturnParameterSetterAndGetter.php',['param'=>$param]) ?>
    <?php echo "\n" ?>
<?php endforeach ?>
<?php echo "\n" ?>
}