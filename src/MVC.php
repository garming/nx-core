<?php
/**
 * Created by PhpStorm.
 * User: garming
 * Date: 06/11/2016
 * Time: 22:49
 */

namespace NxLib\Core;


class MVC
{
    public static function init(string $app_path)
    {
        define("CURRENT_APP_PATH", $app_path);
        static::autoLoader();
    }

    private static function autoLoader()
    {
        spl_autoload_register(function ($class) {
            $current_nx_root = dirname(CURRENT_APP_PATH);
            if (
                strpos($class, 'Controller\\') === 0
                || strpos($class, 'Model\\') === 0
                || strpos($class, 'Config\\') === 0
                || strpos($class, 'Plugin\\') === 0
                || strpos($class, 'Lib\\') === 0
            ) {

                $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                $name = strrchr($file, DIRECTORY_SEPARATOR) . '.php';
                include CURRENT_APP_PATH . DIRECTORY_SEPARATOR . strtolower(dirname($file)) . $name;
            }
            if (
                strpos($class, 'Common\\') === 0

            ) {
                $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                $name = strrchr($file, DIRECTORY_SEPARATOR) . '.php';
                include $current_nx_root . DIRECTORY_SEPARATOR . strtolower(dirname($file)) . $name;
            }
        }, true, true);
    }

}