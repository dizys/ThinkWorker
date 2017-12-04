<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

use think\Config;
use think\Filter;
use think\Log;

if (!function_exists('config')) {
    function config($name = '', $value = null, $range = 'general')
    {
        if (is_null($value) && is_string($name)) {
            return 0 === strpos($name, '?') ? Config::has(substr($name, 1), $range) : Config::get($name, $range);
        } else {
            return Config::set($name, $value, $range);
        }
    }
}

if (!function_exists('filter')) {
    function filter($body)
    {
        return Filter::filt($body);
    }
}

if (!function_exists('json')) {
    function json($body)
    {
        return json_encode($body);
    }
}

if (!function_exists('xml')) {
    function xml($body)
    {
        $root = config("think.xml_root_node");
        $item = config("think.xml_item_node");
        $attr = config("think.xml_root_attr");
        $id = config("think.xml_item_key");
        $encoding = "utf-8";
        return think_core_xml_encode($body, $root, $item, $attr, $id, $encoding);
    }
}

if (!function_exists('jsonp')) {
    function jsonp($body, $callback = null)
    {
        if(is_null($callback)){
            $fallbackCallback = config("think.default_jsonp_handler")?:"jsonpReturn";
            $request = envar("request");
            if($request){
                $setting_var = config("think.jsonp_handler_setting_var");
                $setting_var = $setting_var?:"callback";
                $callback = $request->get($setting_var)?:$fallbackCallback;
                if(!think_core_jsonp_callback_name_check($callback)){
                    $callback = $fallbackCallback;
                }
            }else{
                $callback = $fallbackCallback;
            }
        }
        return $callback."(".json_encode($body).")";
    }
}

if (!function_exists('log')) {
    function log($type, $marker, $msg)
    {
        return Log::log($type, $marker, $msg);
    }
}

if (!function_exists('lang')) {
    function lang($name, ...$vars){
        $lang = envar("lang");
        if($lang){
            return $lang->get($name, ...$vars);
        }
        return $name;
    }
}


if (!function_exists('envar')) {
    function envar($envname)
    {
        $envname = strtolower($envname);
        switch ($envname){
            case "req":
            case "request":
                global $TW_ENV_REQUEST;
                if(isset($TW_ENV_REQUEST)){
                    return $TW_ENV_REQUEST;
                }
                break;
            case "resp":
            case "response":
                global $TW_ENV_RESPONSE;
                if(isset($TW_ENV_RESPONSE)){
                    return $TW_ENV_RESPONSE;
                }
                break;
            case "lang":
                global $TW_ENV_LANG;
                if(isset($TW_ENV_LANG)){
                    return $TW_ENV_LANG;
                }else{
                    return new \think\Lang();
                }
                break;
        }
        return null;
    }
}