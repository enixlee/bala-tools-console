<?php echo "<?php" ?>
<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\YAMLObject\CodeTemplateWriters\ObjectWriter($generateClass)?>

/**
* Created by Generator.
* User: Generator
*/

namespace <?php echo $generateClass->getNameSpace()?>;

use Pluto\Foundation\Serializer\YAMLObject\Creators\ObjectCreator;
use Pluto\Foundation\Serializer\YAMLObject\YAMLObject;

<?php echo $writer->writeClassComment() ?>
<?php echo $writer->writeClassName() ?>
{

<?php echo $writer->writeObjectProperty() ?>
<?php foreach ($generateClass->getParameters() as $param) : ?>
<?php echo $view->render('ObjectSetterAndGetter.php',['param'=>$param]) ?>
<?php echo "\n" ?>
<?php endforeach ?>
<?php echo "\n" ?>
}