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
        $controllerNameSpace[0] = strtoupper($controllerNameSpace[0]);
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

if(!function_exists("think_core_lang_ins")) {
    function think_core_lang_ins()
    {
        global $TW_CORE_LANG;
        if(is_null($TW_CORE_LANG)){
            $TW_CORE_LANG = new \think\Lang();
            $TW_CORE_LANG->loadFromDir(THINK_PATH."lang");
            return $TW_CORE_LANG;
        }else{
            return $TW_CORE_LANG;
        }
    }
}

if(!function_exists("think_core_lang")) {
    function think_core_lang($name, ...$vars)
    {
        $lang = think_core_lang_ins();
        if($lang){
            return $lang->get($name, ...$vars);
        }
        return $name;
    }
}

if(!function_exists("think_core_form_tracing_table_args")) {
    function think_core_form_tracing_table_args($trace)
    {
        $args = "";
        if (isset($trace['args'])) {
            $args = array();
            foreach ($trace['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . $arg . "'";
                } elseif (is_array($arg)) {
                    $args[] = "Array";
                } elseif (is_null($arg)) {
                    $args[] = 'NULL';
                } elseif (is_bool($arg)) {
                    $args[] = ($arg) ? "true" : "false";
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } elseif (is_resource($arg)) {
                    $args[] = get_resource_type($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $args = join(", ", $args);
        }
        return $args;
    }
}

if(!function_exists("think_core_form_tracing_table_filepath")) {
    function think_core_shorten_filepath($filepath)
    {
        $filepath = fix_slashes_in_path($filepath);
        $rootPath = fix_slashes_in_path(ROOT_PATH);
        $find = strpos($filepath, $rootPath);
        if($find === 0){
            $filepath = substr($filepath, strlen($rootPath));
        }
        return $filepath;
    }
}

if(!function_exists("think_core_form_tracing_table_filepath")) {
    function think_core_form_tracing_table_filepath($trace)
    {
        $filepath = "[Internal Function]";
        if (isset($trace['file'])) {
            $filepath = think_core_shorten_filepath($trace['file']);
        }
        return $filepath;
    }
}



if(!function_exists("think_core_form_tracing_table_call")) {
    function think_core_form_tracing_table_call($trace)
    {
        $call = "";
        if (isset($trace['class'])) {
            $call .= $trace['class'];
        }
        if (isset($trace['type'])) {
            $call .= $trace['type'];
        }
        if (isset($trace['function'])) {
            $call .= $trace['function'];
        }
        return $call;
    }
}

if(!function_exists("think_core_get_all_extensions")) {
    function think_core_get_all_extensions()
    {
        $loaded_extensions=get_loaded_extensions();
        return join(", ",$loaded_extensions);
    }
}

if(!function_exists("think_core_charset_auto_revert")) {
    function think_core_charset_auto_revert($msg)
    {
        $encode = mb_detect_encoding($msg, array('ASCII', 'GB2312', 'GBK', 'UTF-8'));
        if ($encode == "GBK") {
            $msg = iconv("GBK", "UTF-8", $msg);
        } else if ($encode == "GB2312") {
            $msg = iconv("GB2312", "UTF-8", $msg);
        } else if ($encode == "EUC-CN") {
            $msg = iconv("GB2312", "UTF-8", $msg);
        }
        return $msg;
    }
}

if(!function_exists("think_core_strrposBack")){
    function think_core_strrposBack($string, $needle, $offset = 0){
        $find = strrpos($string, $needle, $offset);
        if($find === false){
            return false;
        }
        return strlen($string) - $find - strlen($needle);
    }
}

if(!function_exists("think_core_xml_encode")) {
    function think_core_xml_encode($data, $root, $item, $attr, $id, $encoding)
    {
        if (is_array($attr)) {
            $array = [];
            foreach ($attr as $key => $value) {
                $array[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $array);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$root}{$attr}>";
        $xml .= think_core_data_to_xml($data, $item, $id);
        $xml .= "</{$root}>";
        return $xml;
    }
}

if(!function_exists("think_core_data_to_xml")) {
    function think_core_data_to_xml($data, $item, $id)
    {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? think_core_data_to_xml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }
        return $xml;
    }
}

if(!function_exists("think_core_jsonp_callback_name_check")) {
    function think_core_jsonp_callback_name_check($name)
    {
        $pattern = "/^[\.\\\$_0-9a-zA-Z]+$/";
        if (preg_match($pattern, $name)) {
            return true;
        }
        return false;
    }
}

if(!function_exists("think_core_route_basic_path_match")) {
    function think_core_route_basic_path_match($pattern, $uri, &$isMatched, $suffix = "html")
    {
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

if(!function_exists("think_core_route_vars_path_match")) {
    function think_core_route_vars_path_match($pattern, $uri, &$isMatched, $suffix = "html", $varsmap = [])
    {
        $pattern = rtrim($pattern, "/")."/";
        if(substr($uri, -strlen(".".$suffix)) == ".".$suffix){
            if(substr($uri, -strlen(".".$suffix) -1, 1) != "/"){
                $uri = rtrim($uri, ".".$suffix);
            }
        }
        $uri = rtrim(merge_slashes($uri), "/")."/";
        $pattern = preg_quote($pattern, "/");
        $vars = [];
        $pattern = preg_replace_callback("/(\\\{[.\w]+\\\})|(\/\\\:[.\w]+)/", function ($matches) use (&$vars, $varsmap){
            $matched = $matches[0];
            $isBasic = false;
            if(strpos($matched, "\{") === 0){
                $matched = substr($matched, 2, -2);
            }else if(strpos($matched, "/\:") === 0){
                $matched = substr($matched, 3);
                $isBasic = true;
            }else{
                return $matched;
            }
            array_push($vars, $matched);
            if(isset($varsmap[$matched])){
                return "(".$varsmap[$matched].")";
            }else{
                if($isBasic){
                    return "/([-.\w]+)";
                }else{
                    return "([-.\w]+)";
                }
            }
        }, $pattern);
        $pattern = "/^".$pattern."$/";
        $matches = [];
        $result = preg_match($pattern, $uri, $matches);
        if($result){
            $isMatched = true;
            $map = [];
            foreach ($vars as $key => $value){
                if(isset($matches[$key+1])){
                    $map[$value] = $matches[$key+1];
                }
            }
            return $map;
        }else{
            $isMatched = false;
        }
    }
}