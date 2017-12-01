<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


use Throwable;

class HttpException extends \Exception
{
    private $debugOnly, $httpBody="", $statusCode = 400;
    protected $origin = null;
    public function __construct($statusCode, $message = "", $debugOnly = true)
    {
        $this->debugOnly = $debugOnly;
        $this->statusCode = $statusCode;
        parent::__construct($message, 0, null);
    }

    public function setOrigin($e){
        $this->origin = $e;
    }

    public function setHttpBody($content){
        $this->httpBody = $content;
    }

    public function getHttpBody(){
        return $this->httpBody;
    }

    public function getStatusCode(){
        return $this->statusCode;
    }

    public function isDebugOnly(){
        return $this->debugOnly;
    }

    public function loadTemplate($template, $vars = []){
        $template = file_get_contents(THINK_PATH."tpl".DS.$template.".html");
        if($template!=false && is_array($vars)){
            foreach ($vars as $key => $value){
                $template = str_replace("{\$".$key."}", $value, $template);
            }
            return $template;
        }else{
            return "";
        }
    }

}