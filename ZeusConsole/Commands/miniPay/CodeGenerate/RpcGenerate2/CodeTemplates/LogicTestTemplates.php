<?php echo "<?php" ?>

/**
 * Created by Generator.
 * User: Generator
 * toolsVersion:<?php print getConfig('version')."\n"?>
 */

<?php $writer = new \ZeusConsole\Commands\miniPay\CodeGenerate\RpcGenerate2\CodeTemplateWriters\LogicTestTemplatesWriter($generateClass)?>
namespace <?php echo $generateClass->getNameSpace()?>\Tests;
use hellaEngine\support\Http\Http;
use <?php print $writer->writeServiceReturn()?> as ServiceReturn;
use plutoSupports\TestUnit\Params\TestUnitParameter as TestUnitReturn;

/**
 * Class <?php echo $generateClass->getClassTestName()."\n" ?>
 * @package <?php echo $generateClass->getNameSpace()?>\Tests
 */
trait <?php echo $generateClass->getClassTestName()."\n" ?>
{

    /**
     * 获取URL
     * @return string
     */
    protected function get<?php echo $generateClass->getFunctionName()?>Url()
    {
        return "<?php echo $generateClass->getRouteUrl() ?>";
    }
    /**
     * @param array $Params 参数
     * @param bool $dumpUrl 是否dumpURL
     * @param string $method 参数请求方式
     * @param bool $checkResponse 是否检测response
     * @return ServiceReturn
     */
    function callService<?php echo $generateClass->getFunctionName()?>(array $Params = [], $dumpUrl = false, $method = Http::METHOD_GET, $checkResponse = true)
    {
        $response = $this-><?php print $writer->getTestFunctionName()?>(
            $this->get<?php echo $generateClass->getFunctionName()?>Url(),
            $Params,
            $method,
            $dumpUrl,
            $checkResponse
        );
        return $response;
    }

    /**
     * 获取测试参数
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return array
     */
    function getTest<?php echo $generateClass->getFunctionName()?>Parameters(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>)
    {
        $parameters = [
<?php $declare = $writer->getTestParameters();echo join("\n",$declare)."\n"; ?>
        ];

        return $parameters;
    }
    /**
     * 创建测试用例方式
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
     * @return TestUnitReturn
     */
    function create<?php echo $generateClass->getFunctionName()?>TestUnitParameter(
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>)
    {
        $parameters = [
<?php $declare = $writer->getTestParameters();echo join("\n",$declare)."\n"; ?>
        ];
        if (method_exists($this, "creatorTestUnitParameter")) {
            $testUnitParams = $this->creatorTestUnitParameter("<?php echo $generateClass->getRouteUrl() ?>", $parameters)
                ->setCaller($this);
            return $testUnitParams;
        }
        return null;
    }
}