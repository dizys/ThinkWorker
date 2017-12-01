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

if (!function_exists('log')) {
    function log($type, $marker, $msg)
    {
        return Log::log($type, $marker, $msg);
    }
}

if (!function_exists('envar')) {
    function envar($envname)
    {
        $envname = strtolower($envname);
        var_dump($envname);
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