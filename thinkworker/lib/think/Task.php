<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;

use think\task\TaskClient;

abstract class Task
{
    public static function push($method = "fire", $data = null, $maxTryTimes = null, $priority = null, $delay = null){
        $thisRef = new \ReflectionClass(new static);
        $taskPos = think_core_task_analyze($thisRef->getName());
        if(is_null($taskPos)){
            return false;
        }
        return TaskClient::push($taskPos->app, $taskPos->task, $method, $data, $maxTryTimes, $priority, $delay);
    }

    public static function edit($id, $app, $task, $method = "fire", $data = null, $maxTryTimes = null){
        return TaskClient::edit($id, $app, $task, $method, $data, $maxTryTimes);
    }

    public static function shift($id, $delay = 0){
        return TaskClient::shift($id, $delay);
    }

    public static function cancel($id){
        return TaskClient::cancel($id);
    }

    public static function check($id){
        return TaskClient::check($id);
    }

    public static function getWaitingCount(){
        return TaskClient::getWaitingCount();
    }

    public static function getRunningCount(){
        return TaskClient::getRunningCount();
    }

    public static function getFinishedCount(){
        return TaskClient::getFinishedCount();
    }

    public static function getFailedCount(){
        return TaskClient::getFailedCount();
    }

    public static function __callStatic($name, $arguments)
    {
        return TaskClient::$name(...$arguments);
    }
}