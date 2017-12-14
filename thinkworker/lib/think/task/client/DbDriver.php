<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/14
 * Time: 13:44
 */

namespace think\task\client;


use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use think\Db;

class DbDriver implements Driver
{
    protected $connection = "default";

    protected $table = "tasks";

    public function init($config)
    {
        if(!empty($config["db_connection"])){
            $this->connection = $config["db_connection"];
        }
        if(!empty($config["db_table"])){
            $this->table = $config["db_table"];
        }
    }

    public function push($app, $task, $method, $data, $maxTryTimes, $priority, $delay)
    {
        $taskInfo = array();
        $nowTime = time();
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
        $newTask =Db::connection($this->connection)->table($this->table)->insertGetId($taskInfo);
        return $newTask;
    }

    public function edit($id, $app, $task, $method, $data, $maxTryTimes)
    {
        Db::connection($this->connection)->beginTransaction();
        $taskInfo = Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->where("status", "=", "waiting")->lock(true)->first();
        if(is_null($taskInfo)){
            Db::connection($this->connection)->rollBack();
            return false;
        }
        $taskInfo->app = $app;
        $taskInfo->task = $task;
        $taskInfo->method = $method;
        $taskInfo->data = $data;
        $taskInfo->max_try_times = $maxTryTimes;
        try{
            Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->update((array)$taskInfo);
            Db::connection($this->connection)->commit();
            return true;
        }catch (\Throwable $exception){
            Db::connection($this->connection)->rollBack();
            return false;
        }
    }

    public function cancel($id)
    {
        Db::connection($this->connection)->beginTransaction();
        $taskInfo = Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->where("status", "=", "waiting")->lock(true)->first();
        if(is_null($taskInfo)){
            Db::connection($this->connection)->rollBack();
            return false;
        }
        $taskInfo->last_modified_time = time();
        try{
            Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->update((array)$taskInfo);
            Db::connection($this->connection)->commit();
            return true;
        }catch (\Throwable $exception){
            Db::connection($this->connection)->rollBack();
            return false;
        }
    }

    public function shift($id, $delay)
    {
        Db::connection($this->connection)->beginTransaction();
        $taskInfo = Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->where("status", "=", "waiting")->lock(true)->first();
        if(is_null($taskInfo)){
            Db::connection($this->connection)->rollBack();
            return false;
        }
        $taskInfo->delay = $delay;
        $taskInfo->last_modified_time = time();
        try{
            Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->update((array)$taskInfo);
            Db::connection($this->connection)->commit();
            return true;
        }catch (\Throwable $exception){
            Db::connection($this->connection)->rollBack();
            echo describeException($exception);
        }
    }

    public function check($id)
    {
        $taskInfo = Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->first();
        return $taskInfo;
    }

    public function getWaitingCount()
    {
        try{
            return Db::connection($this->connection)->table($this->table)->where("status", "=", "waiting")->count();
        }catch (\Throwable $exception){
            return 0;
        }
    }

    public function getRunningCount()
    {
        try{
            return Db::connection($this->connection)->table($this->table)->where("status", "=", "running")->count();
        }catch (\Throwable $exception){
            return 0;
        }
    }

    public function getFinishedCount()
    {
        try{
            return Db::connection($this->connection)->table($this->table)->where("status", "=", "finished")->count();
        }catch (\Throwable $exception){
            return 0;
        }
    }

    public function getFailedCount()
    {
        try{
            return Db::connection($this->connection)->table($this->table)->where("status", "=", "failed")->count();
        }catch (\Throwable $exception){
            return 0;
        }
    }
}