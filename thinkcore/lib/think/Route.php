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

class Route
{
    protected static $mapRules = [];
    protected static $mapCache = [];
    public static function _init($rules){
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
            throw new RouteNotFoundException("路径不存在");
        }
        $controller = is_array($pathResult)?$pathResult[0]:$pathResult;
        if($cache!=false){
            self::$mapCache[$req->hostname][$req->uri][$req->method] = ['controller' => $controller, 'payload' => $payload];
        }
        return ['controller' => $controller, 'payload' => $payload];
    }

    public static function clearCache(){
        self::$mapCache = [];
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
        self::clearCache();
    }

    private static function addOne($hostname, $pattern, $rule){
        $isHostnameMatched = false;
        foreach (self::$mapRules as $host=>$value){
            $names = explode(",", $host);
            foreach ($names as $name){
                if(wildcardMatch($name,$hostname)){
                    $isHostnameMatched = $host;
                    goto checkout;
                }
            }
        }
        checkout:
        if($isHostnameMatched === false){
            self::$mapRules[$hostname][$pattern] = $rule;
        }else{
            self::$mapRules[$isHostnameMatched][$pattern] = $rule;
        }
    }

    public static function all($hostname, $pattern, $handler, $payloadCheck = null, $cache = true){
        self::add($hostname, $pattern, [$handler, null, $payloadCheck, $cache]);
    }

    public static function get($hostname, $pattern, $handler, $payloadCheck = null, $cache = true){
        self::add($hostname, $pattern, [$handler, 'GET', $payloadCheck, $cache]);
    }

    public static function post($hostname, $pattern, $handler, $payloadCheck = null, $cache = true){
        self::add($hostname, $pattern, [$handler, 'POST', $payloadCheck, $cache]);
    }

    public static function put($hostname, $pattern, $handler, $payloadCheck = null, $cache = true){
        self::add($hostname, $pattern, [$handler, 'PUT', $payloadCheck, $cache]);
    }

    public static function delete($hostname, $pattern, $handler, $payloadCheck = null, $cache = true){
        self::add($hostname, $pattern, [$handler, 'DELETE', $payloadCheck, $cache]);
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

    private static function matchPath($rules, $req, &$payload, &$cache){
        $uri = $req->uri;
        foreach ($rules as $pattern=>$rule){
            if(strpos($pattern, "@")===0){
                $preg = substr($pattern, 1);
                if(preg_match($preg, $uri)){
                    $isMatched = true;
                    if(is_array($rule) && sizeof($rule)>= 2 && !is_null($rule[1]) && !empty($rule[1])){ //2nd Parameter for methods
                        $isMethodMatched = false;
                        $methods = explode(",", $rule[1]);
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
                        break;
                    }
                }
            }else{
                $isMatched = false;
                $suffix = config("think.default_return_type");
                $suffix = is_null($suffix)?"html":$suffix;
                if(is_array($rule) && sizeof($rule)>=4 && !is_null($rule[3]) && !empty($rule[3])){ //4th Parameter for suffix
                    $suffix = trim($rule[3]);
                }
                $map = self::basicPathMatch($pattern, $uri, $isMatched, $suffix);
                if(is_array($rule) && sizeof($rule)>= 2 && !is_null($rule[1]) && !empty($rule[1])){ //2nd Parameter for methods
                    $isMethodMatched = false;
                    $methods = explode(",", $rule[1]);
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
                if(is_array($rule) && sizeof($rule)>=3 && is_array($rule[2])){ //3rd Parameter for payload match
                    foreach ($rule[2] as $key=>$value){
                        if(isset($map[$key])){
                            if(!preg_match($value, $map[$key])){
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
        return false;
    }

    private static function basicPathMatch($pattern, $uri, &$isMatched, $suffix = "html"){
        $pattern = rtrim($pattern, "/")."/";
        if(substr($uri, -strlen(".".$suffix)) == ".".$suffix){
            if(substr($uri, -strlen(".".$suffix) -1, 1) != "/"){
                $uri = rtrim($uri, ".".$suffix);
            }
        }
        $uri = rtrim(merge_slashes($uri), "/")."/";
        $patternLen = strlen($pattern);
        $uriLen = strlen($uri);
        $j = 0; $modeBit = 0; $keyBuffer = ''; $valueBuffer = ''; $map = [];
        for($i=0; $i<$patternLen; $i++){
            if($j>=$uriLen){
                if($i!=$patternLen-1){
                    $modeBit = -1;
                }else{
                    $modeBit = 0;
                }
                break;
            }
            if($modeBit == 0){
                if($pattern[$i]===$uri[$j]){
                    $modeBit = 0;
                    $j++;
                }else if($i-1 >= 0 && $pattern[$i] == ':' && $pattern[$i-1] == '/'){
                    $modeBit = 1;
                    $keyBuffer = '';
                    $valueBuffer = '';
                    while ($uri[$j]!='/' && $j<$uriLen){
                        $valueBuffer.=$uri[$j];
                        $j++;
                    }
                }else{
                    $modeBit = -1;
                    break;
                }
            }else if($modeBit == 1) {
                if($pattern[$i]!='/' && $i<$patternLen){
                    $keyBuffer.=$pattern[$i];
                }
                if($pattern[$i]=='/' || $i == $patternLen - 1){
                    $map[$keyBuffer] = $valueBuffer;
                    $modeBit = 0;
                    $keyBuffer = '';
                    $valueBuffer = '';
                    $j++;
                }
            }
        }
        if($j<$uriLen){
            $modeBit = -1;
        }
        $isMatched = ($modeBit === 0);
        return $map;
    }
}