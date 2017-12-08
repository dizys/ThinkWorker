<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


abstract class Controller
{
    protected $req, $resp;
    protected $view = null;
    protected $beforeActionList = [];

    public function __construct(Request $req, Response $resp)
    {
        $this->req = $req;
        $this->resp = $resp;
        if(!is_null($req->controllerInfo)){
            $appName = $req->controllerInfo->appNameSpace;
            $controllerName = $req->controllerInfo->controllerNameSpace;
            $methodName = $req->controllerInfo->methodName;
            try{
                $this->view = new View($appName, $controllerName."/".$methodName);
            }catch (\Exception $ignored){}
        }
    }

    public function _init(){}

    public function _beforeAction($method){
        foreach ($this->beforeActionList as $key => $value){
            if(is_numeric($key)){
                $this->$value($this->req, $this->resp);
            }else{
                $go = true;
                if(isset($value["except"]) && think_core_in_array_or_string($method, $value["except"])){
                    $go = false;
                }
                if(isset($value["only"])){
                    $go = false;
                    if(think_core_in_array_or_string($method, $value["only"])){
                        $go = true;
                    }
                }
                if($go){
                    $this->$key($this->req, $this->resp);
                }
            }
        }
    }


    public function assign($name, $value = null){
        if($this->view){
            $this->view->assign($name, $value);
        }
    }

    public function clearAssign($name){
        if($this->view){
            $this->view->clearAssign($name);
        }
    }

    public function clearAllAssign(){
        if($this->view){
            $this->view->clearAllAssign();
        }
    }

    public function replace($name, $value = null){
        if($this->view) {
            $this->view->replace($name, $value);
        }
    }

    public function outReplace($name, $value = null){
        if($this->view) {
            $this->view->outReplace($name, $value);
        }
    }

    public function registerFunction($functionName, $asName = null){
        if($this->view) {
            $this->view->registerFunction($functionName, $asName);
        }
    }

    public function fetch($template = null){
        if($this->view){
            return $this->view->fetch($template);
        }else{
            return null;
        }
    }

    public function render($template = null){
        if($this->view){
            $this->resp->send($this->fetch($template));
        }
    }

    public function display($template = null){
        $this->render($template);
    }

    public function getView(){
        return $this->view;
    }
}