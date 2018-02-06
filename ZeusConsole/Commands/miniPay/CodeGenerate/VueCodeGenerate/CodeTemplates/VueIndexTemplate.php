<?php $declare = $generateClass->getImports();echo join("\n",$declare); echo "\n";?>

export {
<?php $declare = $generateClass->getNames();echo join("\n",$declare); echo "\n";?>
};
