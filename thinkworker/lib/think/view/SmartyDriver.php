<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\view;


class SmartyDriver implements Driver
{
    protected $smarty;

    protected $replaceMap = [];

    protected $outReplaceMap = [];

    protected $function_cacheable = true;

    public function init($config)
    {
        if(!class_exists("Smarty")){
            require_once LIB_PATH."smarty".DS."SmartyBC.class.php";
        }
        $this->smarty = new \SmartyBC();
        $this->smarty->compile_dir = CACHE_PATH."smarty".DS."compile".DS;
        $this->smarty->cache_dir = CACHE_PATH."smarty".DS."cache".DS;
        $this->smarty->registerFilter('pre', array($this, "prefilter_replace"));
        $this->smarty->registerFilter('post', array($this, "postfilter_replace"));
        $this->addAllAppViewDir();
        foreach ($config as $key=>$value){
            $this->config($key, $value);
        }
    }

    public function fetch($file)
    {
        return $this->smarty->fetch($file);
    }

    public function assign($name, $value = null)
    {
        if(is_null($value)){
            if(is_array($name)){
                $this->smarty->assign($name);
                return true;
            }else{
                return false;
            }
        }else{
            $this->smarty->assign($name, $value);
            return true;
        }
    }

    public function clearAssign($name){
        $this->smarty->clearAssign($name);
        return true;
    }

    public function clearAllAssign(){
        $this->smarty->clearAllAssign();
        return true;
    }

    public function config($name, $value)
    {
        $name = strtolower(trim($name));
        switch ($name){
            case "caching":
                $this->smarty->caching = $value;
                break;
            case "cache_lifetime":
                $this->smarty->cache_lifetime = $value;
                break;
            case "debugging":
                $this->smarty->debugging = $value;
                break;
            case "debugging_ctrl":
                $this->smarty->debugging_ctrl = $value;
                break;
            case "view_replace_str":
                $this->replace($value);
                break;
            case "allow_php_tag":
                if($value){
                    $this->smarty->php_handling = \Smarty::PHP_ALLOW;
                }else{
                    $this->smarty->php_handling = \Smarty::PHP_PASSTHRU;
                }
                break;
            case "left_delimiter":
                $this->smarty->left_delimiter = $value;
                break;
            case "right_delimiter":
                $this->smarty->right_delimiter = $value;
                break;
            case "function_cacheable":
                $this->function_cacheable = $value;
                break;
        }
    }

    public function getInstance(){
        return $this->smarty;
    }

    public function replace($name, $value = null)
    {
        if(is_array($name) && is_null($value)){
            $this->replaceMap = array_merge($this->replaceMap, $name);
            return true;
        } else if(is_string($name) && is_string($value)){
            $this->replaceMap[$name] = $value;
            return true;
        }
        return false;
    }

    public function outReplace($name, $value = null)
    {
        if(is_array($name) && is_null($value)){
            $this->outReplaceMap = array_merge($this->outReplaceMap, $name);
            return true;
        } else if(is_string($name) && is_string($value)){
            $this->outReplaceMap[$name] = $value;
            return true;
        }
        return false;
    }




    public function prefilter_replace($tpl_source, $template){
        foreach ($this->replaceMap as $name=>$value){
            $tpl_source = str_replace($name, $value, $tpl_source);
        }
        return $tpl_source;
    }

    public function postfilter_replace($tpl_source, $template){
        foreach ($this->replaceMap as $name=>$value){
            $tpl_source = str_replace($name, $value, $tpl_source);
        }
        return $tpl_source;
    }

    public function registerFunction($functionName, $asName = null){
        if(is_null($asName)){
            $asName = $functionName;
        }
        if(is_string($asName)){
            $this->smarty->registerPlugin("function",$asName, $functionName, $this->function_cacheable);
        }
    }

    private function addAllAppViewDir(){
        $request = envar("request");
        $nowApp = null;
        if(!is_null($request) && !is_null($request->controllerInfo) && !is_null($request->controllerInfo->appNameSpace)){
            $nowApp = $request->controllerInfo->appNameSpace;
        }
        $dirs = [];
        foreach (glob(APP_PATH."*".DS) as $appdir){
            $appdir = fix_slashes_in_path($appdir);
            if(is_dir($appdir)){
                $appdir = rtrim($appdir, DS);
                $slashLastPos = strrpos($appdir, DS);
                $appName = substr($appdir, $slashLastPos+1);
                $viewdir = $appdir.DS."view".DS;
                if($appName == $nowApp){
                    array_push($dirs, $viewdir);
                }
                $dirs[$appName] = $viewdir;
            }
        }
        $this->smarty->addTemplateDir($dirs);
    }

}