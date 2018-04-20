<?php echo "<?php" ?>

/**
* Created by Generator.
* User: Generator
*/

namespace <?php echo $generateClass->getNameSpace() ?>;

<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\Generators\DB\CodeTemplateWriters\DBModelTemplateWriter($generateClass) ?>
<?php echo $writer->writeUseDocument() ?>

<?php echo $writer->writeClassComment() ?>
abstract class <?php echo $generateClass->getClassName() ?> extends Model
{
    protected $table = "<?php echo $generateClass->getClassName() ?>";
    protected $primaryKey = "<?php echo $generateClass->getPrimaryKey() ?>";

<?php echo $writer->writeCastsDocument() ?>

}