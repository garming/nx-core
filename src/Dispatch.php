<?php
/**
 * Created by PhpStorm.
 * User: garming
 * Date: 2016/11/8
 * Time: 10:45
 */

namespace NxLib\Core;


class Dispatch
{
    private static $controller;
    private static $action;
    private static $request_method;
    private static $request_uri;
    private static $match_uri;
    private static $params;
    private function __clone(){}
    private function __construct(){}

    public static function run(array $router = [],array $inspectors = [],$server = null)
    {
        static::uriHandler($router,$server);

        if(!empty($inspectors) && is_array($inspectors)){
            foreach ($inspectors as $inspector){
                call_user_func($inspector);
            }
        }
        if(empty(self::$params)){
            call_user_func([(new self::$controller),strtolower(self::$action)]);
        }else{
            call_user_func_array([(new self::$controller),strtolower(self::$action)],self::$params);
        }
    }

    private static function uriHandler(array $router = [],$server = null)
    {
        if(is_null($server)){
            $server = $_SERVER;
        }
        self::$request_method = $server['REQUEST_METHOD'];

        $uri = "/";
        if(isset($server['PATH_INFO'])){
            $uri = isset($server['PATH_INFO']) ? $server['PATH_INFO'] : '/';
        }elseif(isset($server['REQUEST_URI'])){
            $uri = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';
            if(strpos($uri,'?') !== false){
                $uri = mb_substr($uri,0,strpos($uri,'?'));
            }
        }
        $requestMethod = strtoupper($server['REQUEST_METHOD']);
        $check_uri = rtrim($requestMethod."#".$uri,'/');

        self::$request_uri = $uri;
        self::$match_uri = $check_uri;

        if(isset($router[$check_uri])){
            $router = $router[$check_uri];
            $class = $router['class'];
            $action = $router['function'];

            self::$action = $action;
            self::$controller = $class;
            self::$match_uri = $check_uri;

            return;
        }
        if(!empty($router)){
            foreach ($router as $path => $clazz){
                $pos = strrpos($path,"/(");
                if($pos !== false){
                    $pattern    = '/^' . str_replace('/', '#', $path) . '$/';
                    $check_uri  = str_replace('/', '#', $check_uri);
                    $matches    = [];
                    preg_match($pattern, $check_uri, $matches);
                    if ($matches) {
                        array_shift($matches);
                        $class  = $clazz['class'];
                        $action = $clazz['function'];

                        self::$action       = $action;
                        self::$controller   = $class;
                        self::$match_uri    = $path;
                        self::$params       = $matches;

                        return;
                    }
                }
                $pos = strpos($path,"**");
                if($pos !== false){
                    $base_uri = mb_substr($path,0,$pos);
                    if(strpos($check_uri,$base_uri) === 0){
                        $class  = $clazz['class'];
                        $action = $clazz['function'];

                        self::$action       = $action;
                        self::$controller   = $class;
                        self::$match_uri    = $path;

                        return;
                    }
                }
            }
        }
        $uri = explode("/",$uri);
        $controller = (isset($uri[1]) && !empty($uri[1])) ? $uri[1] : 'Index';
        self::$action     = (isset($uri[2]) && !empty($uri[2])) ? $uri[2] : 'index';
        self::$controller = '\\Controller\\'.$controller;

    }
    public static function getController(){
        return self::$controller;
    }
    public static function getAction(){
        return self::$action;
    }
    public static function getRequestMethod(){
        return self::$request_method;
    }

    public static function requestURI()
    {
        return self::$request_uri;
    }

    public static function matchURI()
    {
        return self::$match_uri;
    }
}
