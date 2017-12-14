<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\task\server;


class FileDriver implements Driver
{

    public function init($config)
    {
        // TODO: Implement init() method.
    }

    public function getNextTask()
    {
        $tasks = glob(TEMP_PATH."task".DS."waiting".DS."*.tsk");
        foreach ($tasks as $task){
            $ret = $this->checkRunnableAndMark($task);
            if($ret!=false){
                return $ret;
            }
        }
        return null;
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

    private function checkRunnableAndMark($file){
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
        $nowTime = time();
        if($nowTime - $taskInfo->last_modified_time < $taskInfo->delay){
            return false;
        }
        $taskInfo->delay = 0;
        $taskInfo->status = "running";
        $taskInfo->last_modified_time = $nowTime;
        $fileContent = json($taskInfo);
        rewind($fd);
        $ret = fwrite($fd, $fileContent);
        think_core_release_write($fd);
        if($ret === false){
            return false;
        }
        $ret = $this->moveTo($file, TEMP_PATH."task".DS."running");
        if($ret === false){
            return false;
        }
        return $ret;
    }

    public function finish($id, $data)
    {
        if(!is_file(TEMP_PATH."task".DS."running".DS.$id.".tsk")){
            return false;
        }
        $fd = fopen(TEMP_PATH."task".DS."running".DS.$id.".tsk", "r+");
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
        if($taskInfo->status != "running"){
            return false;
        }
        $nowTime = time();
        $taskInfo->status = "finished";
        $taskInfo->result = $data;
        $taskInfo->last_modified_time = $nowTime;
        $fileContent = json($taskInfo);
        rewind($fd);
        $ret = fwrite($fd, $fileContent);
        think_core_release_write($fd);
        if($ret === false){
            return false;
        }
        $ret = $this->moveTo(TEMP_PATH."task".DS."running".DS.$id.".tsk", TEMP_PATH."task".DS."done".DS."finish");
        if($ret === false){
            return false;
        }
        return true;
    }

    public function fail($id, $stopTrying = false)
    {
        if(!is_file(TEMP_PATH."task".DS."running".DS.$id.".tsk")){
            return false;
        }
        $fd = fopen(TEMP_PATH."task".DS."running".DS.$id.".tsk", "r+");
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
        if($taskInfo->status != "running"){
            return false;
        }
        $nowTime = time();
        if(!$stopTrying && $taskInfo->tried_times < $taskInfo->max_try_times-1){
            $taskInfo->status = "waiting";
            $taskInfo->delay = 0;
            $taskInfo->tried_times = is_null($taskInfo->tried_times)?1:($taskInfo->tried_times+1);
            $taskInfo->last_modified_time = $nowTime;
            $fileContent = json($taskInfo);
            rewind($fd);
            $ret = fwrite($fd, $fileContent);
            think_core_release_write($fd);
            if($ret === false){
                return false;
            }
            $ret = $this->moveTo(TEMP_PATH."task".DS."running".DS.$id.".tsk", TEMP_PATH."task".DS."waiting");
            if($ret === false){
                return false;
            }
            return true;
        }
        $taskInfo->status = "failed";
        $taskInfo->delay = 0;
        $taskInfo->last_modified_time = $nowTime;
        $fileContent = json($taskInfo);
        rewind($fd);
        $ret = fwrite($fd, $fileContent);
        think_core_release_write($fd);
        if($ret === false){
            return false;
        }
        $ret =$this->moveTo(TEMP_PATH."task".DS."running".DS.$id.".tsk", TEMP_PATH."task".DS."done".DS."fail");
        if($ret === false){
            return false;
        }
        return true;
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

    private function moveTo($file, $path){
        $path = fix_slashes_in_path($path);
        $path = rtrim($path, DS).DS;
        $file = fix_slashes_in_path($file);
        $lastDsPos = strrpos($file, DS);
        if($lastDsPos === false && $lastDsPos!=count($file)-1){
            return false;
        }
        $filename = substr($file, $lastDsPos+1);
        $ret = @rename($file, $path.$filename);
        if(!$ret){
            return false;
        }
        $filename = rtrim($filename, ".tsk");
        return $filename;
    }
}