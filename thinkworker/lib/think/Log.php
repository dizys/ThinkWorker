<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


use think\log\FileDriver;

class Log
{
    protected static $driver;
    public static function _init($configs){
        $driverName = isset($configs['driver'])?$configs['driver']:"file";
        $driverName[0] = strtoupper($driverName[0]);
        $engineFullName = "think\\log\\".$driverName."Driver";
        self::$driver = new $engineFullName();
        self::$driver->init($configs);
        var_dump($engineFullName);
    }

    public static function log($type, $marker, $msg){
        $nowTime = time();
        $timeMark = date("H:i:s", $nowTime);
        return self::$driver->write($timeMark." - [ ".$type." ] ".(is_null($marker)?'':("[ "."$marker"." ] ")).$msg."\n");
    }

    public static function i($msg, $marker = null){
        return self::log("info", $marker, $msg);
    }

    public static function e($msg, $marker = null){
        return self::log("error", $marker, $msg);
    }

    public static function w($msg, $marker = null){
        return self::log("warning", $marker, $msg);
    }

    public static function d($msg, $marker = null){
        if(!config("think.debug")){
            return false;
        }
        return self::log("debug", $marker, $msg);
    }
}