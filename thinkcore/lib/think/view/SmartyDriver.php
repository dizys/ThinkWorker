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

    public function init($config)
    {
        if(!class_exists("Smarty")){
            require_once LIB_PATH."smarty".DS."Smarty.class.php";
        }
        $this->smarty = new \Smarty();
        $this->smarty->compile_dir = CACHE_PATH."smarty".DS."compile".DS;
        $this->smarty->cache_dir = CACHE_PATH."smarty".DS."cache".DS;
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
        }
    }

    public function getInstance(){
        return $this->smarty;
    }
}