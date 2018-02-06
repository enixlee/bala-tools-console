<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 15/9/16
 * Time: 下午8:33
 */

/**
 *
 * 默认的翻译器
 * @param string $locate
 * @return Symfony\Component\Translation\Translator
 */
function translator($locate = 'chs')
{
    static $translator;
    if (is_null($translator)) {
        $translator = new \Symfony\Component\Translation\Translator($locate);

    }
    return $translator;
}

/**
 * 用户配置路径
 * @return string
 */
function UserConfigPath()
{
    return \ZeusConsole\Utils\utils::judgePath() . DIRECTORY_SEPARATOR . "configs/config-user.yaml";
}

/**
 * 工作目录
 * @return string
 */
function ZeusConfigPath()
{
    return \ZeusConsole\Utils\utils::judgePath() . DIRECTORY_SEPARATOR . "zeus.config.yaml";
}

/**
 * 获取配置
 * @param null $key 如果此值为空,则返回所有配置
 * @param null $defaultValue key为空的时候返回的值
 * @return array|null
 */
function getConfig($key = null, $defaultValue = null)
{


    $fs = new \Symfony\Component\Filesystem\Filesystem();
    $configs = [];

    $AppConfigPath = __DIR__ . DIRECTORY_SEPARATOR . "configs/config.yaml";

    $AppConfigs = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($AppConfigPath));
    if (!empty($AppConfigs)) {
        $configs = $AppConfigs;
    }

    $UserConfigPath = UserConfigPath();
    if ($fs->exists($UserConfigPath)) {
        $UserConfigs = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($UserConfigPath));
        //合并用户配置
        if (!empty($UserConfigs)) {
            $configs = array_merge($configs, $UserConfigs);
        }
    }

    $ZeusConfigPath = ZeusConfigPath();
    if ($fs->exists($ZeusConfigPath)) {
        $ZeusConfigs = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($ZeusConfigPath));
        //合并用户配置
        if (!empty($ZeusConfigs)) {
            $configs = array_merge($configs, $ZeusConfigs);
        }
    }


    if (is_null($key)) {
        return $configs;
    } else {
        if (isset($configs[$key])) {
            return $configs[$key];
        }
    }
    return $defaultValue;

}

/**
 * 保存配置
 * @param $key
 * @param $value
 * @return mixed
 */
function setConfig($key, $value)
{

    $UserConfigPath = UserConfigPath();

    $fs = new \Symfony\Component\Filesystem\Filesystem();

    $oldValues = getConfig();
    $oldValues[$key] = $value;

    $configString = \Symfony\Component\Yaml\Yaml::dump($oldValues);
    $fs->dumpFile($UserConfigPath, $configString);

    return $value;
}

/**
 * 检测格式是否为yaml
 *
 * @param string $value
 * @return bool
 */
function is_yaml($value)
{
    try {
        \Symfony\Component\Yaml\Yaml::parse($value, true);
    } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
        return false;
    }
    return true;
}