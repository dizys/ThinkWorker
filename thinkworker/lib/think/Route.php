<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;

use think\exception\RouteNotFoundException;
use think\exception\VHostNotFoundException;
use think\route\SubRoute;

class Route
{
    protected static $mapRules = [];
    protected static $mapCache = [];
    protected static $mapCacheSize = 1000;
    public static function _init($rules){
        self::$mapCacheSize = Config::get("think.routing_cache_size");
        self::$mapCacheSize = is_null(self::$mapCacheSize)?1000:self::$mapCacheSize;
        foreach ($rules as $host=>$rule){
            $names = explode(",",$host);
            $i = 0;$len = sizeof($names);
            $newNames = "";
            foreach ($names as $name){
                $name = strtolower(trim($name));
                $newNames .= $name.(($i != $len - 1)?",":"");
                $i++;
            }
            if(is_string($rule)){
                $filename = ROUTE_PATH.$rule.CONF_EXT;
                if(is_file($filename)){
                    $rule = include ($filename);
                }
            }
            self::$mapRules[$newNames] = $rule;
        }
    }

    public static function clearCache($hostname = null){
        if(is_null($hostname)){
            self::$mapCache = [];
        }else if(isset(self::$mapCache[$hostname])){
            self::$mapCache[$hostname] = [];
        }
    }

    public static function add($hostname, $pattern, $rule = null){
        if(is_null($rule) && is_array($pattern)){
            foreach ($pattern as $key => $value){
                self::addOne($hostname, $key, $value);
            }
        }else if(is_array($pattern)){
            foreach ($pattern as $key){
                self::addOne($hostname, $key, $rule);
            }
        }else if(!is_null($rule) && is_string($pattern)){
            self::addOne($hostname, $pattern, $rule);
        }
        self::clearCache($hostname);
    }

    private static function addOne($hostname, $pattern, $rule){
        if(strpos($pattern, "@")===0 && is_array($rule) && count($rule) == 5){
            $rule = [$rule[0], $rule[1], $rule[4]];
        }
        $hostname = self::matchToVHost($hostname);
        if(!isset(self::$mapRules[$hostname])){
            self::$mapRules[$hostname] = [];
        }
        self::$mapRules[$hostname][$pattern] = $rule;
    }

    public static function any($hostname, $pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        self::add($hostname, $pattern, [$handler, null, $payloadCheck, $suffix, $cache]);
    }

    public static function get($hostname, $pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        self::add($hostname, $pattern, [$handler, 'GET', $payloadCheck, $suffix, $cache]);
    }

    public static function post($hostname, $pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        self::add($hostname, $pattern, [$handler, 'POST', $payloadCheck, $suffix, $cache]);
    }

    public static function put($hostname, $pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        self::add($hostname, $pattern, [$handler, 'PUT', $payloadCheck, $suffix, $cache]);
    }

    public static function delete($hostname, $pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        self::add($hostname, $pattern, [$handler, 'DELETE', $payloadCheck, $suffix, $cache]);
    }

    public static function group($hostname, $prefix, $rules, $configs = []){
        $hostname = self::matchToVHost($hostname);
        if(!isset(self::$mapRules[$hostname])){
            self::$mapRules[$hostname] = [];
        }
        $realPrefix = $prefix;
        $realRules = $rules;
        $realConfigs = $configs;
        if(is_array($prefix)){
            $realConfigs = $prefix;
            $realPrefix = null;
        }
        $method = null;
        $suffix = null;
        $cache = null;
        foreach ($realConfigs as $key => $value){
            switch ($key){
                case 0:
                case "method":
                    $method = $value;
                    break;
                case 1:
                case "suffix":
                case "ext":
                    $suffix = $value;
                    break;
                case 2:
                case "cache":
                    $cache = $value;
                    break;
            }
        }
        if($realRules instanceof \Closure){
            $subRoute = new SubRoute($method, $suffix, $cache);
            $realRules($subRoute);
            $realRules = $subRoute->getRules();
        }else if(!is_array($realRules)){
            $realRules = [];
        }
        if(empty($realPrefix)){
            self::$mapRules[$hostname] = array_merge_recursive(self::$mapRules[$hostname], $realRules);
        }else{
            if(!isset(self::$mapRules[$hostname]['['.$realPrefix.']'])){
                self::$mapRules[$hostname]['['.$realPrefix.']'] = [];
            }
            self::$mapRules[$hostname]['['.$realPrefix.']']=array_merge_recursive(self::$mapRules[$hostname]['['.$realPrefix.']'], $realRules);
        }
        self::clearCache($hostname);
    }

    public static function match($req){
        if(isset(self::$mapCache[$req->hostname][$req->uri][$req->method])){
            //Hit routing cache
            return self::$mapCache[$req->hostname][$req->uri][$req->method];
        }
        $vhostResult = self::matchVHost($req->hostname);
        if($vhostResult === false){
            throw new VHostNotFoundException();
        }
        $payload = [];
        $cache = config("think.routing_cache_default");
        $cache = is_null($cache)?true:$cache;
        $pathResult = self::matchPath($vhostResult, $req, $payload, $cache);
        if($pathResult === false || is_null($pathResult) || empty($pathResult)){
            throw new RouteNotFoundException(null, $req->uri);
        }
        $controller = is_array($pathResult)?$pathResult[0]:$pathResult;
        if($cache!=false){
            self::maintainCacheSize($req->hostname);
            self::$mapCache[$req->hostname][$req->uri][$req->method] = ['controller' => $controller, 'payload' => $payload];
        }
        return ['controller' => $controller, 'payload' => $payload];
    }

    private static function maintainCacheSize($hostname){
        if(self::$mapCacheSize != false){
            if(isset(self::$mapCache[$hostname]) && sizeof(self::$mapCache[$hostname]) > self::$mapCacheSize){
                foreach (self::$mapCache[$hostname] as $key=>$value){
                    unset(self::$mapCache[$hostname][$key]);
                    break;
                }
            }
        }
    }


    private static function matchToVHost($hostname){
        foreach (self::$mapRules as $host=>$value){
            $names = explode(",", $host);
            foreach ($names as $name){
                if(wildcardMatch($name,$hostname)){
                    $hostname = $host;
                    goto matchedJump;
                }
            }
        }
        matchedJump:
        return $hostname;
    }

    private static function matchVHost($hostname){
        foreach (self::$mapRules as $host=>$rule){
            $names = explode(",", $host);
            foreach ($names as $name){
                if(wildcardMatch($name,$hostname)){
                    return $rule;
                }
            }
        }
        return false;
    }

    private static function matchPath($rules, $req, &$payload, &$cache, $prefix = null, $uri = null){
        if(is_null($uri)){
            $uri = $req->uri;
        }
        if(!empty($prefix)){
            if(strpos($uri, "/".$prefix) === 0){
                $uri = substr($uri, strlen("/".$prefix));
            }else{
                return false;
            }
        }
        foreach ($rules as $pattern=>$rule){
            if(strpos($pattern, "@")===0){
                $preg = substr($pattern, 1);
                if(preg_match($preg, $uri)){
                    $isMatched = true;
                    if(is_array($rule) && count($rule) == 5){
                        $rule = [$rule[0], $rule[1], $rule[4]];
                    }
                    if(is_array($rule) && sizeof($rule)>= 2 && !is_null($rule[1]) && !empty($rule[1])){ //2nd Parameter for methods
                        $isMethodMatched = false;
                        if(is_array($rule[1])){
                            $methods = $rule[1];
                        }else{
                            $methods = explode(",", $rule[1]);
                        }
                        foreach ($methods as $method){
                            $method = strtoupper(trim($method));
                            if($method == $req->method){
                                $isMethodMatched = true;
                            }
                        }
                        if(!$isMethodMatched){
                            $isMatched = false;
                        }
                    }
                    if($isMatched){
                        if(is_array($rule) && sizeof($rule)>=3){ //3rd Parameter for cache
                            $cache =  ($rule[2]!=false);
                        }
                        return $rule;
                    }
                }
            }else{
                /* Group Routing Recursion */
                if(strpos($pattern, "[")===0 && is_array($rule)){
                    $lastPos = strrpos($pattern, "]");
                    if(!($lastPos === false)){
                        $prefix = substr($pattern, 1, $lastPos-1);
                        //var_dump($rule,$prefix, $uri);
                        $return = self::matchPath($rule,$req, $payload, $cache, $prefix, $uri);
                        if(!($return === false)){
                            return $return;
                        }
                    }
                }
                $isMatched = false;
                $suffix = config("think.default_return_type");
                $suffix = is_null($suffix)?"html":$suffix;
                if(is_array($rule) && sizeof($rule)>=4 && !empty($rule[3]) && !is_null($rule[3]) && !empty($rule[3])){ //4th Parameter for suffix
                    $suffix = trim($rule[3]);
                }
                $vars_path_match = false;
                if(strpos($pattern, "{")!=false && strpos($pattern, "}")!=false){
                    $mapCheck = [];
                    $vars_path_match = true;
                    if(is_array($rule) && sizeof($rule)>=3 && isset($rule[2]) && is_array($rule[2])) { //3rd Parameter for payload match
                        $mapCheck = $rule[2];
                    }
                    $map = think_core_route_vars_path_match($pattern, $uri, $isMatched, $suffix, $mapCheck);
                }else{
                    $map = think_core_route_basic_path_match($pattern, $uri, $isMatched, $suffix);
                }
                if(!$isMatched){
                    continue;
                }
                if(is_array($rule) && sizeof($rule)>= 2 && !is_null($rule[1]) && !empty($rule[1])){ //2nd Parameter for methods
                    $isMethodMatched = false;
                    if(is_array($rule[1])){
                        $methods = $rule[1];
                    }else{
                        $methods = explode(",", $rule[1]);
                    }
                    foreach ($methods as $method){
                        $method = strtoupper(trim($method));
                        if($method == $req->method){
                            $isMethodMatched = true;
                        }
                    }
                    if(!$isMethodMatched){
                        $isMatched = false;
                    }
                }
                if($vars_path_match === false && is_array($rule) && sizeof($rule)>=3 && is_array($rule[2])){ //3rd Parameter for payload match
                    foreach ($rule[2] as $key=>$value){
                        if(isset($map[$key])){
                            if(!preg_match($value, "/^".$map[$key]."$/")){
                                $isMatched = false;
                                break;
                            }
                        }
                    }
                }
                if($isMatched){
                    if(is_array($rule) && sizeof($rule)>=5){ //5th Parameter for cache
                        $cache =  ($rule[4]!=false);
                    }
                    $payload = $map;
                    return $rule;
                    break;
                }
            }
        }
        if(isset($rules["__MISS__"])){
            return $rules["__MISS__"];
        }
        return false;
    }
}