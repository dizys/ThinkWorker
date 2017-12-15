<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

use think\Config;

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
    function describeException(Throwable $e)
    {
        $head = "";
        try {
            $eRef = new ReflectionClass($e);
            $head = "[".$eRef->getShortName()."] ";
        }catch (Throwable $e){

        }
        return $head.$e->getFile()."(".$e->getLine()."): ".$e->getMessage()."\n".$e->getTraceAsString();
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
        $controller = rtrim($controller, "/");
        $appNameSpace = Config::get('think.default_app');
        $appNameSpace = is_null($appNameSpace)?"index":$appNameSpace;
        $controllerNameSpace = Config::get('think.default_controller');
        $controllerNameSpace = is_null($controllerNameSpace)?"Index":$controllerNameSpace;
        $methodName = Config::get('think.default_method');
        $methodName = is_null($methodName)?"index":$methodName;

        $controllerSep = explode("/", $controller);
        $sepSize = sizeof($controllerSep);
        $subController = false;
        if($sepSize>3){
            $controllerSepPre = [];
            $controllerSepPre[0] = $controllerSep[0];
            $controllerSepPre[1] = "";
            for ($i=1; $i<$sepSize-1; $i++){
                $controllerSepPre[1] .= $controllerSep[$i].($i == $sepSize-2?"":"\\");
            }
            $controllerSepPre[2] = $controllerSep[$sepSize-1];
            $controllerSep = $controllerSepPre;
            $subController = true;
        }
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
        if($subController){
            $slashPos = strrpos($controllerNameSpace, "\\");
            $controllerNameSpace[$slashPos+1] = strtoupper($controllerNameSpace[$slashPos+1]);
        }else{
            $controllerNameSpace[0] = strtoupper($controllerNameSpace[0]);
        }
        $appRootNameSpace = Config::get("think.app_namespace");
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

if(!function_exists("think_core_in_array_or_string")) {
    function think_core_in_array_or_string($string, $collection){
        if(is_string($collection)){
            $collection = explode(",", $collection);
        }
        $has = false;
        foreach ($collection as $item){
            $item = trim($item);
            if($item == $string){
                $has = true;
                break;
            }
        }
        return $has;
    }
}

if(!function_exists("think_core_is_win")){
    function think_core_is_win(){
        return strtoupper(substr(PHP_OS,0,3))==='WIN';
    }
}

if(!function_exists("is_null_or_empty")){
    function is_null_or_empty($thing){
        return (!isset($thing)) || is_null($thing) || empty($thing);
    }
}

if(!function_exists("think_core_new_class")){
    function think_core_new_class($classFullName){
        try{
            new $classFullName;
            return true;
        }catch (Throwable $e){
            return false;
        }
    }
}

if(!function_exists("think_core_new_driver")){
    function think_core_new_driver($coreNamespace, $driver){
        $driverNameCore = $driver;
        $driverNameCore[0] = strtoupper($driverNameCore[0]);
        $driverFullName = $coreNamespace."\\".$driverNameCore."Driver";
        try{
            $driverIns = new $driverFullName();
            return $driverIns;
        }catch (\Throwable $e){

        }
        $driverIns = new $driver();
        return $driverIns;
    }
}

if(!function_exists("think_core_get_protected_property")){
    function think_core_get_protected_property(ReflectionClass $reflectionClass, $propertyName, $instance = null){
        $propertyObj = $reflectionClass->getProperty($propertyName);
        if(is_null($propertyObj)){
            return null;
        }
        $restore = true;
        if(!$propertyObj->isPublic()){
            $propertyObj->setAccessible(true);
            $restore = false;
        }
        $value = $propertyObj->getValue($instance);
        $propertyObj->setAccessible($restore);
        return $value;
    }
}

if(!function_exists("think_core_task_analyze")){
    function think_core_task_analyze($taskNamespace){
        $nameRep = explode("\\", $taskNamespace, 4);
        if(count($nameRep)<4){
            return null;
        }
        $appName = $nameRep[1];
        $taskNamespace = $nameRep[3];
        return (object)["app"=>$appName, "task"=>$taskNamespace];
    }
}

if(!function_exists("think_core_can_write")){
    function think_core_can_write($fp){
        $startTime=microtime();
        do{
            $canWrite=flock($fp,LOCK_EX);
            if(!$canWrite){
                usleep(round(rand(0,100)*1000));
            }
        }while((!$canWrite)&&((microtime()-$startTime)<1000));
        return $canWrite;
    }
}

if(!function_exists("think_core_fread")){
    function think_core_fread($fp){
        rewind($fp);
        $content = "";
        do{
            $tmp = fread($fp, 100);
            $content .= $tmp;
        }while(!empty($tmp));
        rewind($fp);
        return $content;
    }
}

if(!function_exists("think_core_release_write")){
    function think_core_release_write($fp){
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

if(!function_exists("think_core_print_error")){
    function think_core_print_error($msg, $description = null){
        echo "====================== ThinkWorker Error =====================\n";
        echo "    Error: ".$msg."\n";
        if($description != null){
            echo  "   ".$description."\n";
        }
        echo "============================================================\n\n";
    }
}

if(!function_exists("think_core_print_info")){
    function think_core_print_info($msg, $description = null){
        echo " [ThinkWorker] [info] ".$msg.(is_null($description)?"":":".$description)."\n";
    }
}

if(!function_exists("think_tool_get_rand_str")){
    function think_tool_get_rand_str($len) {
        srand(think_tool_get_rand_sid());
        $possible="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $str="";
        while(strlen($str)<$len) {
            $str.=substr($possible,(rand()%(strlen($possible))),1);
        }
        return($str);
    }

}

if(!function_exists("think_tool_get_rand_str_all_lower")){
    function think_tool_get_rand_str_all_lower($len) {
        srand(think_tool_get_rand_sid());
        $possible="abcdefghijklmnopqrstuvwxyz1234567890";
        $str="";
        while(strlen($str)<$len) {
            $str.=substr($possible,(rand()%(strlen($possible))),1);
        }
        return($str);
    }

}


if(!function_exists("think_tool_get_rand_sid")){
    function think_tool_get_rand_sid() {
        global $TW_CORE_RAND_SID;
        isset($TW_CORE_RAND_SID) or $TW_CORE_RAND_SID = date("s");
        srand($TW_CORE_RAND_SID);
        $TW_CORE_RAND_SID = rand();
        return $TW_CORE_RAND_SID;
    }
}