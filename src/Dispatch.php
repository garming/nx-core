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
    private function __clone(){}
    private function __construct(){}

    public static function run(array $router = [],array $inspectors = [],$server = null)
    {
        if(!empty($inspectors) && is_array($inspectors)){
            foreach ($inspectors as $inspector){
                call_user_func($inspector);
            }
        }
        if(is_null($server)){
          $server = $_SERVER;
        }
        if(isset($server['PATH_INFO'])){
            $uri = isset($server['PATH_INFO']) ? $server['PATH_INFO'] : '/';
        }elseif(isset($server['REQUEST_URI'])){
            $uri = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '/';
            if(strpos($uri,'?') !== false){
                $uri = mb_substr($uri,0,strpos($uri,'?'));
            }
        }
        $requestMethod = strtoupper($server['REQUEST_METHOD']);
        $check_uri = $requestMethod."#".$uri;
        if(isset($router[$check_uri])){
            $router = $router[$check_uri];
            $class = $router['class'];
            $action = $router['function'];
            call_user_func([(new $class),strtolower($action)]);
            return;
        }
        if(!empty($router)){
            foreach ($router as $path => $clazz){
                $pos = strrpos($path,"/(");
                if($pos !== false){
                    $path = '/^' . str_replace('/', '#', $path) . '$/';
                    $check_uri = str_replace('/', '#', $check_uri);
                    $matches = [];
                    preg_match($path, $check_uri, $matches);
                    if ($matches) {
						array_shift($matches);
                        $class = $clazz['class'];
                        $action = $clazz['function'];
                        call_user_func_array([
                            (new $class),
                            strtolower($action),
                        ], $matches);
                        return;
                    }
                }
            }
        }
        $uri = explode("/",$uri);
        self::$request_method = $server['REQUEST_METHOD'];
        self::$controller = (isset($uri[1]) && !empty($uri[1])) ? $uri[1] : 'Index';
        self::$action     = (isset($uri[2]) && !empty($uri[2])) ? $uri[2] : 'index';
        $controller = '\\Controller\\'.self::$controller;
        $action     = self::$action;
        if(defined("RESTFUL") && RESTFUL){
            $action = ucfirst($action);
            $class = $controller.'\\'.$action;
            call_user_func([(new $class),strtolower(self::$request_method)]);
            return;
        }
        call_user_func([(new $controller),$action]);

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
}
