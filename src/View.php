<?php
/**
 * Created by PhpStorm.
 * User: garming
 * Date: 06/11/2016
 * Time: 22:43
 */

namespace NxLib\Core;


class View
{
    private static $dataSet = [];

    public function __construct($file)
    {
        echo $this->render($file);
        exit;
    }


    /**
     * Set an array of values
     *
     * @param $key
     * @param $value
     * @internal param array $array of values
     */
    public static function set($key,$value)
    {
        static::$dataSet[$key] = $value;
    }

    public static function render($file)
    {
        $file = str_replace('/',DIRECTORY_SEPARATOR,$file);
        try {
            ob_start();
            extract(static::$dataSet);
            require CURRENT_APP_PATH .DIRECTORY_SEPARATOR.MVC::getViewPath().DIRECTORY_SEPARATOR. $file . '.php';
            return ob_get_clean();
        }
        catch(\Exception $e)
        {
            return '';
        }
    }
}