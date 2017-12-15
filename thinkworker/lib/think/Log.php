<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;

use think\log\Driver;

class Log
{
    /**
     * @var null|Driver
     */
    protected static $driver = null;

    /**
     * Log initialization method
     *
     * @param array $configs
     * @return void
     */
    public static function _init($configs){
        $driverName = isset($configs['driver'])?$configs['driver']:"file";
        self::$driver = think_core_new_driver("think\\log", $driverName);
        self::$driver->init($configs);
    }

    /**
     * Common logging
     *
     * @param string $type
     * @param string $marker
     * @param string $msg
     * @return bool
     */
    public static function log($type, $marker, $msg){
        if(is_null(self::$driver)){
            return false;
        }
        $nowTime = time();
        $timeMark = date("H:i:s", $nowTime);
        return self::$driver->write($timeMark." - [ ".$type." ] ".(is_null($marker)?'':("[ "."$marker"." ] ")).$msg."\n");
    }

    /**
     * Info logging
     *
     * @param string $msg
     * @param string|null $marker
     * @return bool
     */
    public static function i($msg, $marker = null){
        return self::log("info", $marker, $msg);
    }

    /**
     * Error logging
     *
     * @param string $msg
     * @param string|null $marker
     * @return bool
     */
    public static function e($msg, $marker = null){
        return self::log("error", $marker, $msg);
    }

    /**
     * Warning logging
     *
     * @param string $msg
     * @param string|null $marker
     * @return bool
     */
    public static function w($msg, $marker = null){
        return self::log("warning", $marker, $msg);
    }

    /**
     * Debug logging
     *
     * @param string $msg
     * @param string|null $marker
     * @return bool
     */
    public static function d($msg, $marker = null){
        if(!config("think.debug")){
            return false;
        }
        return self::log("debug", $marker, $msg);
    }
}