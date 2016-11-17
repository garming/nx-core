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
    private static $instance = null;

    private function __construct()
    {
        //further for more
    }
    private function __clone(){}

    public static function init()
    {
        if(!is_null(self::$instance)){
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Set an array of values
     *
     * @param $key
     * @param $value
     * @internal param array $array of values
     */
    public function set($key,$value)
    {
        $this->$key = $value;
    }


    public function display($file)
    {
        echo $this->render($file);
        exit;
    }

    public function render($file)
    {
        $file = str_replace('/',DIRECTORY_SEPARATOR,$file);
        try {
            ob_start();
            extract((array) $this);
            require CURRENT_APP_PATH .DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR. $file . '.php';
            return ob_get_clean();
        }
        catch(\Exception $e)
        {
            return '';
        }
    }
}