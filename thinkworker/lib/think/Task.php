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
    /**
     * Push one task into the queue
     *
     * @param string $method
     * @param mixed|null $data
     * @param int|null $maxTryTimes
     * @param int|null $priority
     * @param int|null $delay
     * @return bool
     */
    public static function push($method = "fire", $data = null, $maxTryTimes = null, $priority = null, $delay = null){
        $thisRef = new \ReflectionClass(new static);
        $taskPos = think_core_task_analyze($thisRef->getName());
        if(is_null($taskPos)){
            return false;
        }
        return TaskClient::push($taskPos->app, $taskPos->task, $method, $data, $maxTryTimes, $priority, $delay);
    }

    /**
     * Edit one waiting task
     *
     * @param string $id
     * @param string $app
     * @param string $task
     * @param string $method
     * @param mixed|null $data
     * @param int|null $maxTryTimes
     * @return bool
     */
    public static function edit($id, $app, $task, $method = "fire", $data = null, $maxTryTimes = null){
        return TaskClient::edit($id, $app, $task, $method, $data, $maxTryTimes);
    }

    /**
     * Shift one waiting task
     *
     * @param string $id
     * @param int $delay
     * @return bool
     */
    public static function shift($id, $delay = 0){
        return TaskClient::shift($id, $delay);
    }

    /**
     * Cancel one waiting task
     *
     * @param string $id
     * @return bool
     */
    public static function cancel($id){
        return TaskClient::cancel($id);
    }

    /**
     * Check status and info of a task
     *
     * @param string $id
     * @return null
     */
    public static function check($id){
        return TaskClient::check($id);
    }

    /**
     * Get number of tasks that are waiting
     *
     * @return int
     */
    public static function getWaitingCount(){
        return TaskClient::getWaitingCount();
    }

    /**
     * Get number of tasks that are running
     *
     * @return int
     */
    public static function getRunningCount(){
        return TaskClient::getRunningCount();
    }

    /**
     * Get number of tasks that are finished
     *
     * @return int
     */
    public static function getFinishedCount(){
        return TaskClient::getFinishedCount();
    }

    /**
     * Get number of tasks that are failed
     *
     * @return int
     */
    public static function getFailedCount(){
        return TaskClient::getFailedCount();
    }

    /**
     * Dynamically pass method to TaskClient
     *
     * @param string $name
     * @param array ...$arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return TaskClient::$name(...$arguments);
    }
}