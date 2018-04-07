<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 * toolsVersion:<?php print getConfig('version')."\n"?>
 */

namespace <?php echo $generateClass->getNameSpace()?>\RPC;

<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters\LogicTemplatesWriter($generateClass)?>
<?php echo $writer->writeUseDocument()?>

/**
 *
 * <?php echo $generateClass->getDescription() . "\n"?>
 * url:<?php echo $generateClass->getRouteUrl() . "\n"?>
 * <?php echo $generateClass->getClassName() . "\n"?>
 * @package <?php echo $generateClass->getNameSpace()?>\RPC
 */
trait <?php echo $generateClass->getClassName()."\n" ?>
{
    /**
     * @var string
     */
    public static $<?php echo $generateClass->getClassName()?>RpcUrl = '<?php echo $generateClass->getRouteUrl() ?>';
    /**
     * @var string
     */
    public static $<?php echo $generateClass->getClassName()?>RpcFunctionName = '<?php echo $generateClass->getFunctionName()?>';
    /**
     * @var string
     */
    public static $<?php echo $generateClass->getClassName()?>RpcType = '<?php echo $generateClass->getRpcType()?>';
    /**
     * @var string
     */
    public static $<?php echo $generateClass->getClassName()?>XicServiceName = '<?php echo $writer->getXicServiceName()?>';
    /**
     * @var array
     */
    public static $<?php echo $generateClass->getClassName()?>Parameters = [
<?php $declare = $writer->getStaticParameters();echo join("\n",$declare)."\n"; ?>
    ];

<?php echo $writer->writeOptions()?>

    /**
     * <?php echo $generateClass->getDescription() . "\n"?>
<?php
if($generateClass->isDeprecated())
{
    echo "     * @deprecated\n";
}
?>
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return RPCCommandResult
     */
    public function <?php echo $generateClass->getFunctionName()?>(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>)
    {
<?php
if($generateClass->isDeprecated())
{
    echo "        logicErrorInterfaceDeprecated();\n";
}
?>

<?php $declare = $generateClass->getParameterCheck();echo join("\n",$declare); ?>

        return $this->Do<?php echo $generateClass->getFunctionName()?>(<?php $declare = $generateClass->getParameterTransfer();echo join(", ",$declare);?>);
    }

    /**
     * <?php echo $generateClass->getDescription() . "\n"?>
<?php
if($generateClass->isDeprecated())
{
    echo "     * @deprecated\n";
}
?>
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return RPCCommandResult
     */
    protected function Do<?php echo $generateClass->getFunctionName()?>(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare);?>
)
    {
        return $this->callLogicFunction(self::$<?php echo $generateClass->getClassName()?>RpcFunctionName,
            [
<?php $declare = $generateClass->getParameterAsArrayWithParameterVar();echo join(",\n",$declare)."\n"; ?>
            ]);
    }


    /**
     * <?php echo $generateClass->getDescription() . "\n"?>
<?php
if($generateClass->isDeprecated())
{
    echo "     * @deprecated\n";
}
?>
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return RpcCallerParameter
     */
    static public function with<?php echo $generateClass->getFunctionName()?>(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>)
    {
<?php
if($generateClass->isDeprecated())
{
    echo "        logicErrorInterfaceDeprecated();\n";
}
?>

<?php $declare = $generateClass->getParameterCheck();echo join("\n",$declare); ?>

        $parameter = RpcCallerParameter::create('<?php echo $generateClass->getRouteUrl() ?>',
            [
<?php $declare = $generateClass->getParameterAsArrayWithParameterVar();echo join(",\n",$declare)."\n"; ?>
            ]);
<?php echo $writer->writeWithParameters()?>
        return $parameter;
    }


}