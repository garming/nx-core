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

    public static function run(array $router = [],array $inspectors = [])
    {
        if(!empty($inspectors) && is_array($inspectors)){
            foreach ($inspectors as $inspector){
                call_user_func($inspector);
            }
        }
        $uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        if(isset($router[$requestMethod."#".$uri])){
            $router = $router[$requestMethod."#".$uri];
            $class = $router['class'];
            $action = $router['function'];
            call_user_func([(new $class),strtolower($action)]);
            return;
        }
        $uri = explode("/",$uri);
        self::$request_method = $_SERVER['REQUEST_METHOD'];
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