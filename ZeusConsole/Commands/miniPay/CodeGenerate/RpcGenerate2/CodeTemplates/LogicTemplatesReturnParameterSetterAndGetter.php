<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters\LogicTemplatesReturnParameterSetterAndGetterWriter($param) ?>
<?php echo $writer->writeVariable()?>

    /**
     * @return <?php echo $param->getVariableCommentString(). "\n";?>
     */
    public function get<?php echo $param->getFunctionName()?>()
    {
        return $this->getObjectData('<?php echo $param->getName();?>', null);
    }
<?php echo $writer->writeSetFunction() ?>
<?php echo $writer->writeResetFunction() ?>
<?php if($param->isRepeated()) echo $writer->writeAddFunction() ?>

