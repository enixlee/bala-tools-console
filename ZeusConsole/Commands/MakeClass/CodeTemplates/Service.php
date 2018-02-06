namespace service;

use Common\Util\Common_Util_ReturnVar;

/**
 *
 * @auther zhipeng
 */
class service_<?php echo $className ?> extends service_base {
    function __construct()
    {
        $this->services_enable([]);
    }
	public function isNeedLogin() {
		return <?php echo $needLogin ?>;
	}

}