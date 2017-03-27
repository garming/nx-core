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
    private static $view_path;
    public static function init(string $app_path,$autoLoadMap = [],$viewsPath = [])
    {
        define("CURRENT_APP_PATH", $app_path);
        define("CURRENT_AUTOLOAD_MAP", $autoLoadMap);
        define("CURRENT_VIEWS_PATH", $viewsPath);
        static::autoLoader();
    }

    private static function autoLoader()
    {
        spl_autoload_register(function ($class) {
            if(!empty(CURRENT_VIEWS_PATH)){
                $current_namespace = dirname($class)."\\";
                $current_views_path = CURRENT_VIEWS_PATH;
                if(isset($current_views_path[$current_namespace])){
                    static::$view_path = $current_views_path[$current_namespace];
                }
            }

            $current_nx_root = CURRENT_APP_PATH;
            if (
                strpos($class, 'Controller\\') === 0
                || strpos($class, 'Model\\') === 0
                || strpos($class, 'Config\\') === 0
                || strpos($class, 'Plugin\\') === 0
                || strpos($class, 'Lib\\') === 0
            ) {

                $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                $name = ucfirst(trim(strrchr($file, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR)) . '.php';
                include $current_nx_root . DIRECTORY_SEPARATOR . strtolower(dirname($file)) . DIRECTORY_SEPARATOR . $name;
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
                        $file_path = str_replace($name.'.php',"",$file_path.".php");
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
    public static function getViewPath(){
        return static::$view_path;
    }
}