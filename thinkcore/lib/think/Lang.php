<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


class Lang
{
    protected $lang, $app;
    protected $langRules = [];
    public function __construct($lang=null, $app = null)
    {
        if(is_null($lang)){
            $this->lang = config("think.default_lang");
        }else{
            $this->lang = $lang;
        }
        $this->app = $app;
        $this->langPackPrepare();
    }

    private function langPackPrepare(){
        $defaultPackFile = LANG_PATH.$this->lang.CONF_EXT;
        if(is_file($defaultPackFile)){
            $defaultPack = include($defaultPackFile);
            if($defaultPack!=false){
                $this->set($defaultPack);
            }
        }
        if(!is_null($this->app)){
            $appPackFile = APP_PATH.$this->app.DS."lang".DS.$this->lang.CONF_EXT;
            if(is_file($appPackFile)){
                $appPack = include($appPackFile);
                if($appPack!=false){
                    $this->set($appPack);
                }
            }
        }
    }

    public function load($file){
        if(is_file($file)){
            $lang = include($file);
            if($lang != false && is_array($lang)){
                $this->set($lang);
                return true;
            }
        }
        return false;
    }

    public function set($name, $text=null){
        if(is_array($name) && is_null($text)){
            $this->langRules = array_merge($this->langRules, $name);
        }else if(is_string($name) && !is_null($text)){
            $this->langRules[$name] = $text;
        }
    }

    public function unset($name){
        if(is_array($name)){
            foreach ($name as $item){
                unset($this->langRules[$item]);
            }
        }else{
            unset($this->langRules[$name]);
        }
    }

    public function unsetAll(){
        $this->langRules = [];
    }

    public function get($name, ...$vars){
        if(!isset($this->langRules[$name]) || is_null($this->langRules[$name])){
            return $name;
        }
        $varNum = sizeof($vars);
        if($varNum == 0){
            return $this->langRules[$name];
        }else if($varNum == 1 && is_array($vars[0])){
            $text = $this->langRules[$name];
            foreach ($vars[0] as $key=>$value){
                $text = str_replace("{\$".$key."}", $value, $text);
            }
            return $text;
        }else{
            $text = $this->langRules[$name];
            for($i =0; $i<$varNum; $i++){
                $text = str_replace("{\$".($i+1)."}", $vars[$i], $text);
            }
            return $text;
        }
    }

    public function getLang(){
        return $this->lang;
    }

}