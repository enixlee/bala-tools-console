<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters\LogicTemplatesReturnParameterSetterAndGetterWriter($param) ?>
<?php echo $writer->writeVariable()?>

    /**
     * @return <?php echo $param->getVariableCommentString(). "\n";?>
     */
    public function get<?php echo $param->getFunctionName()?>()
    {
        return $this-><?php echo $param->getName();?>;
    }

<?php echo $writer->writeSetFunction() ?>
<?php if($param->isRepeated()) echo $writer->writeAddFunction() ?>

