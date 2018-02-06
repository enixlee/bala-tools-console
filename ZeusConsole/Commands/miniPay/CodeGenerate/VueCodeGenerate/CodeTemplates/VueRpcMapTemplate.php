<?php $declare = $generateClass->getRpcTypesImport();echo join("\n",$declare); echo "\n";?>

const rpc = {
<?php $declare = $generateClass->getNames();echo join("\n",$declare); echo "\n";?>
};

let RpcMap = {};
<?php $declare = $generateClass->getMap();echo join("\n",$declare); echo "\n";?>

let Vue = window.getVue();
Vue.prototype.$rpc = rpc;

export default RpcMap;
