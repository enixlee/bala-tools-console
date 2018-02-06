<?php $declare = $generateClass->getImportsErrorCode();echo join("\n",$declare); echo "\n";?>

let CodesMap = {};
<?php $declare = $generateClass->getErrorCodeMap();echo join("\n",$declare); echo "\n";?>

export default CodesMap;
