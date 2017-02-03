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
    public static function init(string $app_path,$autoLoadMap = [])
    {
        define("CURRENT_APP_PATH", $app_path);
        define("CURRENT_AUTOLOAD_MAP", $autoLoadMap);
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
                $name = ucfirst(trim(strrchr($file, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR)) . '.php';
                include CURRENT_APP_PATH . DIRECTORY_SEPARATOR . strtolower(dirname($file)) . DIRECTORY_SEPARATOR . $name;
            }
            if (
                strpos($class, 'Common\\') === 0

            ) {
                $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                $name = ucfirst(trim(strrchr($file, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR)) . '.php';
                include $current_nx_root . DIRECTORY_SEPARATOR . strtolower(dirname($file)) . DIRECTORY_SEPARATOR . $name;
            }

            if(!empty(CURRENT_AUTOLOAD_MAP)){
                foreach (CURRENT_AUTOLOAD_MAP as $namespace => $paths){
                    if(strpos($class, $namespace) === 0){
                        $file_path = str_replace($namespace,"",$class);
                        $name = explode("\\",$class);
                        $name = end($name);
                        $file_path = str_replace($name,"",$file_path);
                        $file_path = str_replace("\\",DIRECTORY_SEPARATOR,$file_path);
                        foreach ($paths as $path){
                            $file = $current_nx_root.DIRECTORY_SEPARATOR.strtolower($path.DIRECTORY_SEPARATOR.$file_path).$name.'.php';
                            include $file;
                            break;
                        }
                        break;
                    }
                }
            }
        }, true, true);
    }

}