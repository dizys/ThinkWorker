<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\task;


use think\task\client\Driver;

class TaskClient
{
    /**
     * @var null|Driver
     */
    protected static $driver = null;
    /**
     * @var int
     */
    protected static $maxTryTimes = 4;

    public static function _init($config){
        $driverName = isset($config['client_driver'])?$config['client_driver']:"file";
        self::$driver = think_core_new_driver("think\\task\\client", $driverName);
        self::$driver->init($config);
        self::$maxTryTimes = isset($config['default_max_try_times'])?$config['default_max_try_times']:4;
    }

    public static function push($app, $task, $method = "fire", $data = null, $maxTryTimes = null, $priority = 50, $delay = 0){
        if(self::$driver){
            $maxTryTimes = is_null($maxTryTimes)?self::$maxTryTimes:$maxTryTimes;
            $method = is_null($method)?"fire":$method;
            $priority = is_null($priority)?50:$priority;
            $delay = is_null($delay)?0:$delay;
            return self::$driver->push($app, $task, $method, $data, $maxTryTimes, $priority, $delay);
        }
        return false;
    }

    public static function edit($id, $app, $task, $method = "fire", $data = null, $maxTryTimes = null){
        if(self::$driver){
            $maxTryTimes = is_null($maxTryTimes)?self::$maxTryTimes:$maxTryTimes;
            $method = is_null($method)?"fire":$method;
            return self::$driver->edit($id, $app, $task, $method, $data, $maxTryTimes);
        }
        return false;
    }

    public static function shift($id, $delay = 0){
        if(self::$driver){
            $delay = is_null($delay)?0:$delay;
            return self::$driver->shift($id, $delay);
        }
        return false;
    }

    public static function cancel($id){
        if(self::$driver){
            return self::$driver->cancel($id);
        }
        return false;
    }

    public static function check($id){
        if(self::$driver){
            return self::$driver->check($id);
        }
        return null;
    }

    public static function getWaitingCount(){
        if(self::$driver){
            return self::$driver->getWaitingCount();
        }
        return 0;
    }

    public static function getRunningCount(){
        if(self::$driver){
            return self::$driver->getRunningCount();
        }
        return 0;
    }

    public static function getFinishedCount(){
        if(self::$driver){
            return self::$driver->getFinishedCount();
        }
        return 0;
    }

    public static function getFailedCount(){
        if(self::$driver){
            return self::$driver->getFailedCount();
        }
        return 0;
    }

}