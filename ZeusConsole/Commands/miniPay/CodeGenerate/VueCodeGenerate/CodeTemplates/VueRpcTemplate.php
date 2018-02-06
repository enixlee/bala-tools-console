/**
 * Created by Generator.
 * Author: Generator
 * description: <?php echo $generateClass->description() . "\n" ?>
 */

let Vue = window.PMApp.Vue;
let Rpc = Vue.prototype.getPlugin('HttpRequest');
let debug = Vue.prototype.getPlugin('Debug');
<?php if(count($generateClass->getParameterCheck())>0){echo "let tc = Vue.prototype.getPlugin('TypeCheck');"; echo "\n";} ?>
<?php if(count($generateClass->getParameterDeclares())>0){echo "let lodash = Vue.prototype.getPlugin('lodash');"; echo "\n";} ?>

export const <?php echo $generateClass->fileName() ?>Method = '<?php echo $generateClass->getRoute() ?>'<?php echo ";\n" ?>

export const <?php echo $generateClass->fileName() ?>RpcType = '<?php echo $generateClass->getRpcType() ?>'<?php echo ";\n" ?>

/**
 *
<?php $declare = $generateClass->getParameterDocuments();echo join("\n",$declare); echo "\n"?>
 * @returns {*}
 * @constructor
 */
export function <?php echo $generateClass->fileName()?> (
<?php $declare = $generateClass->getParameterDeclares();echo join(",\n",$declare); ?>) {
<?php $declare = $generateClass->getParameterCheck();echo join("\n",$declare); ?>
<?php if(count($generateClass->getParameterCheck())>0){echo "\n";} ?>
  let params = {};
<?php $declare = $generateClass->checkParameterNull();echo join("\n",$declare); ?>

  if (debug.isProduction()) {<?php echo "\n" ?>
    return Rpc.<?php echo $generateClass->getMethod() ?>(<?php echo $generateClass->fileName() ?>Method, params);
  } else {
    let mockEngine = Vue.prototype.getPlugin('RpcMockEngine');
    if (Vue.prototype.getPlugin('lodash').isObject(mockEngine)) {
      let data = mockEngine[<?php echo $generateClass->fileName() ?>Method];
      if (Vue.prototype.getPlugin('lodash').isObject(data)) {
        return new Promise(function (resolve, reject) {
          let rpcResult = Vue.prototype.getPlugin('RpcCommandResultMaker')(
            {
              request: {},
              status: 200,
              config: {},
              data: {
                command_name: <?php echo $generateClass->fileName() ?>Method,
                description: 'succ',
                code: 'succ',
                data: data,
                succ: true
              }
            }
          );
          resolve(rpcResult);
        });
      } else {
        return Rpc.get(<?php echo $generateClass->fileName() ?>Method, params);
      }
    }
  }
}
