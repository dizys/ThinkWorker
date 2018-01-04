<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


use think\view\Driver;

class View
{
    /**
     * @var Driver
     */
    protected $template;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $fileExt;

    /**
     * @var string
     */
    protected $appName;

    /**
     * View constructor.
     *
     * @param string $appName
     * @param string|null $viewfile
     * @param array|null $values
     */
    public function __construct($appName, $viewfile = null, $values = null)
    {
        $this->enginePrepare();
        if(is_null($viewfile) || is_array($viewfile)){
            if(!strpos($appName, "@")){
                $viewfile = think_core_rtrim($appName, ".".$this->fileExt).".".$this->fileExt;
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
        if(is_array($viewfile)){
            $this->assign($viewfile);
        }else if (is_array($values)){
            $this->assign($values);
        }
    }

    /**
     * Prepare template engine
     *
     * @return void
     */
    private function enginePrepare(){
        $configs = config("template");
        $engine = $configs['engine'];
        $this->template = think_core_new_driver("think\\view", $engine);
        $this->template->init($configs);
        $fileExt = config('template.tpl_ext');
        $this->fileExt = is_null($fileExt)?"html":$fileExt;
    }

    /**
     * Prepare template file path
     *
     * @param string $appName
     * @param string $viewfile
     * @return void
     */
    private function filepathPrepare($appName, $viewfile){
        $this->filePath = $this->parseTemplateFilepath($appName, $viewfile);
    }

    /***
     * Convert app name and view file to real file path
     *
     * @param string $appName
     * @param string $viewfile
     * @return string
     */
    private function parseTemplateFilepath($appName, $viewfile){
        $viewfile = think_core_rtrim($viewfile, ".".$this->fileExt);
        return fix_slashes_in_path(APP_PATH.$appName.DS."view".DS.$viewfile.".".$this->fileExt);
    }

    /**
     * Replace the content of the template
     *
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function replace($name, $value = null){
        $this->template->replace($name, $value);
    }

    /**
     * Replace the content of the View rendering output
     *
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function outReplace($name, $value = null){
        $this->template->outReplace($name, $value);
    }

    /**
     * Assign a variable value or multiple values for View
     *
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function assign($name, $value = null){
        $this->template->assign($name, $value);
    }

    /**
     * Clear the assign for a variable value or multiple values
     *
     * @param string|array $name
     * @return void
     */
    public function clearAssign($name){
        $this->template->clearAssign($name);
    }

    /**
     * Clear all the assigns for View
     *
     * @return void
     */
    public function clearAllAssign(){
        $this->template->clearAllAssign();
    }

    /**
     * Register template-usable function for View
     *
     * @param callable $callable
     * @param string|null $asName
     * @return void
     */
    public function registerFunction($callable, $asName = null){
        $this->template->registerFunction($callable, $asName);
    }

    /**
     * Get the html output
     *
     * @param string|null $template
     * @return string
     */
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

    /**
     * Config template engine
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function config($name, $value){
        $this->template->config($name, $value);
    }

    /**
     * Dynamically get property
     *
     * @param string $name
     * @return Driver
     */
    public function __get($name)
    {
        if($name == "driver"){
            return $this->template;
        }
    }
}