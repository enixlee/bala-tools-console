<?php
require __DIR__ . '/../vendor/autoload.php';


class ClassLoader
{
    /**
     * singleton
     */
    private static $_instance;

    private function __construct()
    {
        // echo 'This is a Constructed method;';
        $this->includePath = dirname(dirname(__FILE__)) . '/';
    }

    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    // 单例方法,用于访问实例的公共的静态方法
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }

    private $classMap = array();
    private $includePath = '';

    public function register()
    {
        spl_autoload_register(array(
            $this,
            'loadClass'
        ), true, false);
    }

    public function unregister()
    {
        spl_autoload_unregister(array(
            $this,
            'loadClass'
        ));
    }

    public function loadClass($className)
    {
        if ('\\' == $className [0]) {
            $className = substr($className, 1);
        }

        if (isset ($this->classMap [$className])) {
            return $this->classMap [$className];
        }

        $filename = $className . '.php';

        $path = $this->includePath . $filename;
        $path = strtr($path, '\\', DIRECTORY_SEPARATOR);

        // echo $path;
        if (is_file($path)) {

            $succ = include_once($path);
            $this->classMap [$className] = $succ;

            return $succ;
        } else {
            // dump ( $classname );
            $this->classMap [$className] = false;
        }
    }
}

date_default_timezone_set('PRC');

ClassLoader::getInstance()->register();
