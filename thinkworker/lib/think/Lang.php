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
    /**
     * @var string
     */
    protected $lang;

    /**
     * @var string
     */
    protected $app;

    /**
     * @var array
     */
    protected $langRules = [];

    /**
     * Lang constructor.
     *
     * @param string|null $lang
     * @param string|null $app
     */
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

    /**
     * Prepare the language pack
     *
     * @return void
     */
    private function langPackPrepare(){
        $this->loadFromDir(LANG_PATH);
        if(!is_null($this->app)){
            $this->loadFromDir( APP_PATH.$this->app.DS."lang");
        }
    }

    /**
     * Load in a specific language pack file
     *
     * @param string $file
     * @return bool
     */
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

    /**
     * Load from a directory, while the filename is automatically decided by current language name
     *
     * @param $dirpath
     * @return bool
     */
    public function loadFromDir($dirpath){
        $dirpath = fix_slashes_in_path($dirpath);
        $dirpath = rtrim($dirpath,DS).DS;
        $result = $this->load($dirpath.$this->lang.CONF_EXT);
        if($result){
            return true;
        }else{
            return $this->load($dirpath.config("think.default_lang").CONF_EXT);
        }
    }

    /**
     * Set a language pack item or multiple items in this object
     *
     * @param string|array $name
     * @param string|null $text
     * @return void
     */
    public function set($name, $text=null){
        if(is_array($name) && is_null($text)){
            $this->langRules = array_merge($this->langRules, $name);
        }else if(is_string($name) && !is_null($text)){
            $this->langRules[$name] = $text;
        }
    }

    /**
     * Unset a language pack item or multiple items in this object
     *
     * @param string|array $name
     * @return void
     */
    public function unset($name){
        if(is_array($name)){
            foreach ($name as $item){
                unset($this->langRules[$item]);
            }
        }else{
            unset($this->langRules[$name]);
        }
    }

    /**
     * Unset all items in this object
     *
     * @return void
     */
    public function unsetAll(){
        $this->langRules = [];
    }

    /**
     * Get a language pack item text
     *
     * @param string $name
     * @param array ...$vars
     * @return string
     */
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

    /**
     * Get current language name
     *
     * @return string
     */
    public function getLang(){
        return $this->lang;
    }

}