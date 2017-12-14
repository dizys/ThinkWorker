<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/10
 * Time: 22:53
 */

namespace think\server;


use think\Config;
use think\MainServer;
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
        $ret = think_core_new_class("app\\".$singleServer);
        if($ret){
            return true;
        }
        $serverSep = explode("\\", $singleServer);
        if(count($serverSep) == 2){
            $ret = think_core_new_class("app\\".$serverSep[0]."\\server\\".$serverSep[1]);
            if($ret){
                return true;
            }
        }
        think_core_print_error("Can't find the single server referring to: ".$singleServer);
        return false;
    }
}