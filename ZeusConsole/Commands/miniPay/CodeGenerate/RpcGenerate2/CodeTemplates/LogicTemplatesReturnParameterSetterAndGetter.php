<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters\LogicTemplatesReturnParameterSetterAndGetterWriter($param) ?>
<?php echo $writer->writeVariable()?>

    /**
     * @return <?php echo $param->getVariableCommentString(). "\n";?>
     */
    public function get<?php echo $param->getFunctionName()?>()
    {
        return $this-><?php echo $param->getName();?>;
    }

    /**
     * @param <?php echo $param->getVariableCommentString()?> $<?php echo $param->getName(). "\n";?>
     */
    public function set<?php echo $param->getFunctionName()?>(<?php if(!$param->isMessage()){printf("%s ", $param->getTypeDeclareAsString());}?>$<?php echo $param->getName();?> = null)
    {
        <?php
        if($param->isMessage() && !$param->isRepeated())
        {
            printf("if (is_array(\$%s)) {\n",$param->getName());
            printf("            \$%s = %s::formArray(\$%s);\n",$param->getName(),$param->getTypeDeclareAsString(),$param->getName());
            printf("        }\n",$param->getName());
        }
        else
        {
            printf("\n");
        }
        ?>
        $this-><?php echo $param->getName();?> = $<?php echo $param->getName();?>;
    }

<?php if($param->isRepeated()) echo $writer->writeAddFunction() ?>

