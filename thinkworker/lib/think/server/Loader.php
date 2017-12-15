<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\server;


use think\Config;
use think\Db;
use think\MainServer;
use think\Redis;
use think\Server;
use think\Session;
use think\task\TaskServer;
use Workerman\Worker;

class Loader
{
    public static function load(){
        $singleServer = self::isSingleServer();
        if($singleServer!=false){
            self::loadSingleServer($singleServer);
            return;
        }
        if(think_core_is_win()){
            think_core_print_error("Windows does not support multi workers!");
            return;
        }

        foreach(glob(APP_PATH.'*'.DS."server".DS."*".EXT) as $serverFile)
        {
            $fullClassName = self::resolveNamespace($serverFile);
            try{
                new $fullClassName();
            }catch (\Exception $e){
                think_core_print_error("Failed to load: ".$fullClassName);
            }
        }

        if(Config::get("task.enable")){
            new TaskServer();
        }
    }

    public static function resolveNamespace($serverFile){
        $serverFile = fix_slashes_in_path($serverFile);
        $serverFileLen = strlen($serverFile);
        $lastDsPos = strrpos($serverFile, DS);
        $serverName = substr($serverFile, $lastDsPos + 1);
        $serverName = rtrim($serverName, EXT);
        $secLastDsPos = strrpos($serverFile, DS, -($serverFileLen - $lastDsPos)-1);
        $thrLastDsPos = strrpos($serverFile, DS, -($serverFileLen-$secLastDsPos)-1);
        $appName = substr($serverFile, $thrLastDsPos+1, $secLastDsPos-$thrLastDsPos-1);
        $namespace = 'app\\'.$appName."\\server\\".$serverName;
        return $namespace;
    }

    public static function runAll(){
        Worker::runAll();
    }

    public static function isSingleServer(){
        global $argv;
        foreach ($argv as $key=>$arg){
            if(strtolower($arg) == "--single-server"){
                if(isset($argv[$key+1])){
                    $server = $argv[$key+1];
                    $server = str_replace("/", "\\", $server);
                    return $server;
                }
                return false;
            }
        }
        return false;
    }

    public static function loadSingleServer($singleServer){
        switch ($singleServer){
            case "TaskServer":
                think_core_new_class("think\\task\\TaskServer");
                break;
            case "MainServer":
                MainServer::_init(Config::get("worker_engine"));
                break;
            default:
                self::loadUserSingleServer($singleServer);
                break;
        }
        return;
    }

    public static function loadUserSingleServer($singleServer){
        $ret = think_core_new_class($singleServer);
        if($ret){
            return true;
        }
        $appRootNameSpace = Config::get("think.app_namespace");
        $appRootNameSpace = is_null($appRootNameSpace)?"app":$appRootNameSpace;
        $ret = think_core_new_class($appRootNameSpace."\\".$singleServer);
        if($ret){
            return true;
        }
        $serverSep = explode("\\", $singleServer);
        if(count($serverSep) == 2){
            $ret = think_core_new_class($appRootNameSpace."\\".$serverSep[0]."\\server\\".$serverSep[1]);
            if($ret){
                return true;
            }
        }
        think_core_print_error("Can't find the single server referring to: ".$singleServer);
        return false;
    }


    public static function loadEssentials($object = null){
        $load_db = true; $load_redis = true; $load_app = true; $load_session = true;
        if(!is_null($object) && $object instanceof Server){
            $server = new \ReflectionClass($object);
            $load_db = think_core_get_protected_property($server, "load_db", $object);
            $load_db = is_null($load_db)?true: $load_db;
            $load_redis = think_core_get_protected_property($server, "load_redis", $object);
            $load_redis = is_null($load_redis)?true: $load_redis;
            $load_session = think_core_get_protected_property($server, "load_session", $object);
            $load_session = is_null($load_session)?true: $load_session;
            $load_app = think_core_get_protected_property($server, "load_app", $object);
            $load_app = is_null($load_app)?true: $load_app;
        }
        if($load_db){
            Db::_init_by_worker_process(Config::get(null, "database"));
        }
        if($load_redis){
            Redis::_init(Config::get(null, "redis"));
        }
        if($load_session){
            Session::_init(Config::get("session"));
        }
        if($load_app && is_file(APP_PATH . "app.php")){
            require_once APP_PATH . "app.php";
        }
    }
}