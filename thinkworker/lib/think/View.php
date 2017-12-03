<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


class View
{
    protected $template;
    protected $filePath, $fileExt, $appName;
    public function __construct($appName, $viewfile = null)
    {
        $this->enginePrepare();
        if(is_null($viewfile)){
            if(!strpos($appName, "@")){
                $viewfile = rtrim($appName, ".".$this->fileExt).".".$this->fileExt;
                $viewfile = fix_slashes_in_path($viewfile);
                if(is_file($viewfile)){
                    $this->filePath = $viewfile;
                }else{
                    $request = envar("request");
                    if(!is_null($request) && !is_null($request->controllerInfo) && !is_null($request->controllerInfo->appNameSpace)){
                        $nowApp = $request->controllerInfo->appNameSpace;
                        $filepath = APP_PATH.$nowApp.DS."view".DS.$viewfile;
                        if(is_file($filepath)){
                            $this->filePath = $filepath;
                        }
                    }
                }
            }else{
                $info = explode("@", $appName, 2);
                $num = sizeof($info);
                if($num == 2){
                    $appName = $info[0];
                    $viewfile = $info[1];
                    $this->filepathPrepare($appName, $viewfile);
                    $this->appName = $appName;
                }else{
                    $appName = $info[0];
                    $this->filePath = $appName;
                }
            }
        }else{
            $this->filepathPrepare($appName, $viewfile);
            $this->appName = $appName;
        }
    }

    private function enginePrepare(){
        $configs = config("template");
        $engine = $configs['engine'];
        $engine[0] = strtoupper($engine[0]);
        $engineFullName = "think\\view\\".$engine."Driver";
        $this->template = new $engineFullName();
        $this->template->init($configs);
        $fileExt = config('template.tpl_ext');
        $this->fileExt = is_null($fileExt)?"html":$fileExt;
    }

    private function filepathPrepare($appName, $viewfile){
        $this->filePath = $this->parseTemplateFilepath($appName, $viewfile);
    }

    private function parseTemplateFilepath($appName, $viewfile){
        $viewfile = rtrim($viewfile, ".".$this->fileExt);
        return fix_slashes_in_path(APP_PATH.$appName.DS."view".DS.$viewfile.".".$this->fileExt);
    }

    public function replace($name, $value = null){
        $this->template->replace($name, $value);
    }

    public function assign($name, $value = null){
        $this->template->assign($name, $value);
    }

    public function clearAssign($name){
        $this->template->clearAssign($name);
    }

    public function clearAllAssign(){
        $this->template->clearAllAssign();
    }

    public function fetch($template = null){
        if(is_null($template)){
            return $this->template->fetch($this->filePath);
        }else{
            $sep = explode("@", $template);
            if(sizeof($sep) == 1){
                $appName= $this->appName;
                $viewfile = trim($sep[0]);
                $filePath = $this->parseTemplateFilepath($appName, $viewfile);
                return $this->template->fetch($filePath);
            }else if(sizeof($sep) == 2){
                $appName= trim($sep[0]);
                $viewfile = trim($sep[1]);
                $filePath = $this->parseTemplateFilepath($appName, $viewfile);
                return $this->template->fetch($filePath);
            }else{
                return $this->template->fetch($this->filePath);
            }
        }
    }

    public function config($name, $value){
        $this->template->config($name, $value);
    }

    public function __get($name)
    {
        if($name == "driver"){
            return $this->template;
        }
    }
}