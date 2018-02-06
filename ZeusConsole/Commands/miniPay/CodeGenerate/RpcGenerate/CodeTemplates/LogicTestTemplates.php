<?php echo "<?php" ?>

/**
 * Created by Generator.
 */

namespace App\Route\CodeTemplates;
use hellaEngine\support\Http\Http;
use <?php
switch ($generateClass->getRpcType()){
    case "client":
        print 'hellaEngine\support\Http\HttpResponse';
        break;
    case "system":
        print 'hellaEngine\support\Http\HttpResponse';
        break;
    case "core":
        print 'Pluto\Foundation\RPC\RPCCommandResult';
        break;
    case "SDKDeveloper":
        print 'hellaEngine\support\Http\HttpResponse';
        break;
    case "merchantSdkClient":
        print 'hellaEngine\support\Http\HttpResponse';
        break;
    default:
        print 'hellaEngine\support\Http\HttpResponse';
        break;
}?> as ServiceReturn;

/**
 * Class <?php echo $generateClass->getClassTestName()."\n" ?>
 * @package App\Route\CodeTemplates
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
     * @return ServiceReturn
     */
    function callService<?php echo $generateClass->getFunctionName()?>(array $Params = [], $dumpUrl = false, $method = Http::METHOD_GET)
    {
        $response = $this-><?php
switch ($generateClass->getRpcType()){
    case "client":
        print "callServiceWithClientToken";
        break;
    case "system":
        print "callServiceWithSystemToken";
        break;
    case "customMerchant":
        print "callServiceWithSystemToken";
        break;
    case "core":
        print "callServiceWithRpcCommand";
        break;
    case "SDKDeveloper":
        print "callServiceWithClientToken";
        break;
    case "merchantSdkClient":
        print "callServiceWithMerchantSDKClientToken";
        break;
    default:
        print "undefined function:".$generateClass->getRpcType();
        break;
}?>(
            $this->get<?php echo $generateClass->getFunctionName()?>Url(),
            $Params,
            $method,
            $dumpUrl
        );
        return $response;
    }

    /**
     * 获取测试参数
     * @return array
     */
    function getTest<?php echo $generateClass->getFunctionName()?>Parameters()
    {
        $parameters = [
<?php $declare = $generateClass->getParameterAsArray();echo join(",\n",$declare)."\n"; ?>
        ];

        return $parameters;
    }
}