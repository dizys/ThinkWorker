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

    /**
     * Controller constructor.
     *
     * @param Request $req
     * @param Response $resp
     */
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

    /**
     * User defined Controller initialization method, ready for override
     *
     * @return void
     */
    public function _init(){}

    /**
     * First called before any user defined method is called
     *
     * @param string $method
     */
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

    /**
     * Assign a variable value or multiple values for the bound View
     *
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function assign($name, $value = null){
        if($this->view){
            $this->view->assign($name, $value);
        }
    }

    /**
     * Clear the assign for a variable value or multiple values of the bound View
     *
     * @param string|array $name
     * @return void
     */
    public function clearAssign($name){
        if($this->view){
            $this->view->clearAssign($name);
        }
    }

    /**
     * Clear all the assigns for the bound View
     *
     * @return void
     */
    public function clearAllAssign(){
        if($this->view){
            $this->view->clearAllAssign();
        }
    }

    /**
     * Replace the content of the template for the bound View
     *
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function replace($name, $value = null){
        if($this->view) {
            $this->view->replace($name, $value);
        }
    }

    /**
     * Replace the content of the bound View rendering output
     *
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function outReplace($name, $value = null){
        if($this->view) {
            $this->view->outReplace($name, $value);
        }
    }

    /**
     * Register template-usable function for the bound View
     *
     * @param callable $callable
     * @param string|null $asName
     * @return void
     */
    public function registerFunction($callable, $asName = null){
        if($this->view) {
            $this->view->registerFunction($callable, $asName);
        }
    }

    /**
     * Get the html output from the bound View
     *
     * @param string|null $template
     * @return string|null
     */
    public function fetch($template = null){
        if($this->view){
            return $this->view->fetch($template);
        }else{
            return null;
        }
    }

    /**
     * Render and send html output to the client
     *
     * @param string|null $template
     * @return void
     */
    public function render($template = null){
        if($this->view){
            $this->resp->send($this->fetch($template));
        }
    }

    /**
     * Render and send html output to the client, alias for `render`
     *
     * @param string|null $template
     * @return void
     */
    public function display($template = null){
        $this->render($template);
    }

    /**
     * Get the bound View
     *
     * @return null|View
     */
    public function getView(){
        return $this->view;
    }
}