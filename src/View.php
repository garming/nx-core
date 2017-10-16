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
    private static $layoutDataSet = [];
    private static $layout = '';

    public function __construct($file)
    {

        $content = $this->render($file);
        if(empty(static::$layout)){
            $layout_path = CURRENT_APP_PATH .DIRECTORY_SEPARATOR.MVC::getViewPath();
            $layout_path = dirname($layout_path);
            static::$layout = $layout_path.DIRECTORY_SEPARATOR.'_layout'.DIRECTORY_SEPARATOR.'layout.php';
        }
        if(file_exists(static::$layout)){
            static::$layoutDataSet['content'] = $content;
            echo static::compile(static::$layout,static::$layoutDataSet);
        }else{
            echo $content;
        }
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
        $views_path = CURRENT_APP_PATH .DIRECTORY_SEPARATOR.MVC::getViewPath();
        $file = str_replace('/',DIRECTORY_SEPARATOR,$file);
        $file = $views_path.DIRECTORY_SEPARATOR.$file.'.php';
        return self::compile($file,static::$dataSet);
    }
    private static function compile($absolute_file_path,$data){
        try {
            ob_start();
            extract($data);
            require $absolute_file_path;
            return ob_get_clean();
        }
        catch(\Exception $e)
        {
            return '';
        }
    }
    public static function setLayout($file_path){
        static::$layout = $file_path;
    }
    public static function layout($key,$value){
        static::$layoutDataSet[$key] = $value;
    }
}