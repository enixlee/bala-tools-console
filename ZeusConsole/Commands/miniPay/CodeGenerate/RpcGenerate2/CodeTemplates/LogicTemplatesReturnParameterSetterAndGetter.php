<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters\LogicTemplatesReturnParameterSetterAndGetterWriter($param) ?>
<?php echo $writer->writeVariable()?>

<?php echo $writer->writeGetFunction()?>

<?php echo $writer->writeSetFunction() ?>
<?php echo $writer->writeResetFunction() ?>
<?php if($param->isRepeated()) echo $writer->writeAddFunction() ?>

