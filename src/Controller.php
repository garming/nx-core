<?php
/**
 * Created by PhpStorm.
 * User: garming
 * Date: 06/11/2016
 * Time: 22:41
 */

namespace NxLib\Core;


class Controller
{
    protected $noParamMsg = '参数不存在';
    private $allParams;
    private $rawParams;

    private function __clone()
    {
    }

    protected function sendJsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function getParam($key, $default = '', $msg = '')
    {
        if (isset($_GET[$key])) {
            return $this->typeResult($_GET[$key],$default);
        }
        $this->throwPramsNotExist($msg);
        return $default;
    }

    protected function postParam($key, $default = '', $msg = '')
    {
        if (isset($_POST[$key])) {
            return $this->typeResult($_POST[$key],$default);
        }
        $this->throwPramsNotExist($msg);
        return $default;
    }

    protected function putParam($key, $default = '', $msg = '')
    {
        if(is_null($this->rawParams)){
            $raw_data = file_get_contents('php://input', 'r');
            if(isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'],'multipart/form-data;') === 0){
                //content-type=multipart/form-data;
                $this->rawParams = $this->raw_multipart_form_data_handler($raw_data);
            }else{
                //other content-type
                parse_str($raw_data,$this->rawParams);
            }

        }
        if(isset($this->rawParams[$key])){
            return $this->typeResult($this->rawParams[$key],$default);
        }
        $this->throwPramsNotExist($msg);
        return $default;
    }

    protected function deleteParam($key, $default = '', $msg = '')
    {
        return $this->putParam($key,$default,$msg);
    }

    protected function params()
    {
        if (!is_null($this->allParams)) {
            return $this->allParams;
        }
        switch ($_SERVER['REQUEST_METHOD']){
            case 'GET':
                return $_GET;
            case 'POST':
                return array_merge($_GET,$_POST);
            default:
                $this->initRawParams();
                return array_merge($_GET,$_POST,$this->rawParams);
        }
    }

    protected function param($key, $default = '', $msg = '')
    {
        $params = $this->params();
        if (isset($params[$key])) {
            return $params[$key];
        }
        $this->throwPramsNotExist($msg);
        return $default;
    }

    protected function header($key)
    {
        $key = 'http_' . $key;
        $key = str_replace('-', '_', $key);
        $key = strtoupper($key);
        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    private function throwPramsNotExist($msg)
    {
        if ($msg === true) {
            throw new ParamException($this->noParamMsg);
        }
        if (!empty($msg)) {
            throw new ParamException($msg);
        }
    }

    private function typeResult($value, $var)
    {
        switch (gettype($var)) {
            case 'boolean':
                return boolval($value);
            case 'integer':
                return intval($value);
            case 'double':
                return doubleval($value);
            case 'string':
                return strval($value);
            default:
                return $value;
        }
    }
    private function raw_multipart_form_data_handler($raw_data)
    {
        $handler_data = [];
        $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));
        $parts = array_slice(explode($boundary, $raw_data), 1);
        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") break;

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );
                list(, $type, $name) = $matches;
                isset($matches[4]) and $filename = $matches[4];

                // handle your fields here
                switch ($name) {
                    // this is a file upload
                    case 'userfile':
                        file_put_contents($filename, $body);
                        break;

                    // default for all other files is to populate $data
                    default:
                        $handler_data[$name] = substr($body, 0, strlen($body) - 2);
                        break;
                }
            }
        }
        return $handler_data;
    }
    private function initRawParams(){
        if(!is_null($this->rawParams)){
            return;
        }
        $raw_data = file_get_contents('php://input', 'r');
        $this->rawParams = $this->raw_multipart_form_data_handler($raw_data);
    }
}