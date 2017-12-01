<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */
if(!function_exists("wildcardMatch")){
    function wildcardMatch($pattern, $value)
    {
        if ($pattern == $value) return true;

        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern) . '\z';
        return (bool) preg_match('#^' . $pattern . '#', $value);
    }
}

if(!function_exists("describeException")){
    function describeException(Exception $e)
    {
        return $e->getFile()."(".$e->getLine()."): ".$e->getMessage()."\n".$e->getTraceAsString();
    }
}

if(!function_exists("merge_slashes")) {
    function merge_slashes($string)
    {
        return preg_replace("/\/(?=\/)/", "\\1", $string);
    }
}

if(!function_exists("think_controller_analyze")) {
    function think_controller_analyze($controller)
    {
        $controllerSep = explode("/", $controller);
        $appNameSpace = config('think.default_app');
        $appNameSpace = is_null($appNameSpace)?"index":$appNameSpace;
        $controllerNameSpace = config('think.default_controller');
        $controllerNameSpace = is_null($controllerNameSpace)?"Index":$controllerNameSpace;
        $methodName = config('think.default_method');
        $methodName = is_null($methodName)?"index":$methodName;

        if(isset($controllerSep[2])){
            $appNameSpace = $controllerSep[0];
            $controllerNameSpace = $controllerSep[1];
            $methodName = $controllerSep[2];
        }else if(isset($controllerSep[1])){
            $controllerNameSpace = $controllerSep[0];
            $methodName = $controllerSep[1];
        }else if(isset($controllerSep[0]) && !empty($controllerSep[0])){
            $methodName = $controllerSep[0];
        }
        $appRootNameSpace = config("app_namespace");
        $appRootNameSpace = is_null($appRootNameSpace)?"app":$appRootNameSpace;
        $classFullName = $appRootNameSpace."\\".$appNameSpace."\\controller\\".$controllerNameSpace;
        return (object)[
            'appRootNamespace' => $appRootNameSpace,
            'appNameSpace' => $appNameSpace,
            'controllerNameSpace' => $controllerNameSpace,
            'methodName' => $methodName,
            'classFullName' => $classFullName
        ];
    }
}

if(!function_exists("fix_slashes_in_path")) {
    function fix_slashes_in_path($path)
    {
        if("/" == DS){
            return str_replace("\\", DS, $path);
        }else{
            return str_replace("/", DS, $path);
        }
    }
}

