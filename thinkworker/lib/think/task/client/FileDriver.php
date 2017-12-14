<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\task\client;


class FileDriver implements Driver
{

    public function init($config)
    {
        // TODO: Implement init() method.
    }

    public function push($app, $task, $method, $data, $maxTryTimes, $priority, $delay)
    {
        $taskInfo = array();
        $nowTime = time();
        $priority = self::formPriority($priority);
        $taskInfo["app"] = $app;
        $taskInfo["task"] = $task;
        $taskInfo["method"] = is_null($method)?"fire":$method;
        $taskInfo["data"] = $data;
        $taskInfo["max_try_times"] = $maxTryTimes;
        $taskInfo["tried_times"] = 0;
        $taskInfo["priority"] = $priority;
        $taskInfo["delay"] = $delay;
        $taskInfo["created_time"] = $nowTime;
        $taskInfo["last_modified_time"] = $nowTime;
        $taskInfo["status"] = "waiting";
        $taskInfo["result"] = null;
        $taskInfo["hash_key"] = think_tool_get_rand_str(16);
        $fileContent = json($taskInfo);
        $contentHash = strtolower(md5($fileContent));
        $id = $priority."_".$nowTime."_".$contentHash;
        $filename = $id.".tsk";
        $ret = file_put_contents(TEMP_PATH."task".DS."waiting".DS.$filename, $fileContent);
        if($ret === false){
            return false;
        }
        return $id;
    }


    public function edit($id, $app, $task, $method, $data, $maxTryTimes)
    {
        if(!is_file(TEMP_PATH."task".DS."waiting".DS.$id.".tsk")){
            return false;
        }
        $fd = fopen(TEMP_PATH."task".DS."waiting".DS.$id.".tsk", "r+");
        if($fd === false){
            return false;
        }
        if(!think_core_can_write($fd)){
            return false;
        }
        $taskInfo = think_core_fread($fd);
        if($taskInfo === false){
            return false;
        }
        $taskInfo = json_decode($taskInfo);
        if(is_null($taskInfo)){
            return false;
        }
        if($taskInfo->status != "waiting"){
            return false;
        }
        if($app){
            $taskInfo->app = $app;
        }
        if($task){
            $taskInfo->task = $task;
        }
        if($method){
            $taskInfo->method = $method;
        }
        $taskInfo->data = $data;
        if($maxTryTimes){
            $taskInfo->maxTryTimes = $maxTryTimes;
        }
        $fileContent = json($taskInfo);
        rewind($fd);
        $ret = fwrite($fd, $fileContent);
        think_core_release_write($fd);
        if($ret === false){
            return false;
        }
        return true;
    }

    public function cancel($id)
    {
        $file = TEMP_PATH."task".DS."waiting".DS.$id.".tsk";
        if(!is_file($file)){
            return false;
        }
        $fd = fopen($file, "r+");
        if($fd === false){
            return false;
        }
        if(!think_core_can_write($fd)){
            return false;
        }
        $taskInfo = think_core_fread($fd);
        if($taskInfo === false){
            return false;
        }
        $taskInfo = json_decode($taskInfo);
        if(is_null($taskInfo)){
            return false;
        }
        if($taskInfo->status != "waiting"){
            return false;
        }
        $taskInfo->status = "canceled";
        $taskInfo->last_modified_time = time();
        $fileContent = json($taskInfo);
        rewind($fd);
        $ret = fwrite($fd, $fileContent);
        think_core_release_write($fd);
        if($ret === false){
            return false;
        }
        @unlink($file);
        return true;
    }

    public function shift($id, $delay)
    {
        $file = TEMP_PATH."task".DS."waiting".DS.$id.".tsk";
        if(!is_file($file)){
            return false;

        }
        $fd = fopen($file, "r+");
        if($fd === false){
            return false;
        }
        if(!think_core_can_write($fd)){
            return false;
        }
        $taskInfo = think_core_fread($fd);
        if($taskInfo === false){
            return false;
        }
        if($taskInfo === false){
            return false;
        }
        $taskInfo = json_decode($taskInfo);
        if(is_null($taskInfo)){
            return false;
        }
        if($taskInfo->status != "waiting"){
            return false;
        }
        $taskInfo->delay = $delay;
        $taskInfo->last_modified_time = time();
        $fileContent = json($taskInfo);
        rewind($fd);
        $ret = fwrite($fd, $fileContent);
        think_core_release_write($fd);
        if($ret === false){
            return false;
        }
        return true;
    }

    public function check($id)
    {
        $taskInfo = @file_get_contents(TEMP_PATH."task".DS."waiting".DS.$id.".tsk");
        if($taskInfo === false){
            $taskInfo = @file_get_contents(TEMP_PATH."task".DS."running".DS.$id.".tsk");
        }
        if($taskInfo === false){
            $taskInfo = @file_get_contents(TEMP_PATH."task".DS."done".DS."finish".DS.$id.".tsk");
        }
        if($taskInfo === false){
            $taskInfo = @file_get_contents(TEMP_PATH."task".DS."done".DS."fail".DS.$id.".tsk");
        }
        if($taskInfo === false){
            return null;
        }
        $taskInfo = json_decode($taskInfo);
        return $taskInfo;
    }

    public function getWaitingCount()
    {
       $files = glob(TEMP_PATH."task".DS."waiting".DS."*.tsk");
       return count($files);
    }

    public function getRunningCount()
    {
        $files = glob(TEMP_PATH."task".DS."running".DS."*.tsk");
        return count($files);
    }

    public function getFinishedCount()
    {
        $files = glob(TEMP_PATH."task".DS."done".DS."finish".DS."*.tsk");
        return count($files);
    }

    public function getFailedCount()
    {
        $files = glob(TEMP_PATH."task".DS."done".DS."fail".DS."*.tsk");
        return count($files);
    }

    public static function formPriority($priority){
        $priority = intval($priority);
        if($priority<10 && $priority>=1){
            $priority = "0".$priority;
        }else if($priority>=10 && $priority< 100){
            $priority = strval($priority);
        }else{
            $priority = "01";
        }
        return $priority;
    }
}