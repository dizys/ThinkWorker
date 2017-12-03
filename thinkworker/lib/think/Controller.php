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

    public function __construct(Request $req, Response $resp)
    {
        $this->req = $req;
        $this->resp = $resp;
        if(!is_null($req->controllerInfo)){
            $appName = $req->controllerInfo->appNameSpace;
            $controllerName = $req->controllerInfo->controllerNameSpace;
            $methodName = $req->controllerInfo->methodName;
            $this->view = new View($appName, $controllerName."/".$methodName);
        }
    }

    public function _init(){}

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