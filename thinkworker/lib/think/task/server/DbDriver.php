<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/14
 * Time: 15:45
 */

namespace think\task\server;


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

    public function getNextTask()
    {
        Db::connection($this->connection)->beginTransaction();
        $nowTime = time();
        $taskInfo = Db::connection($this->connection)->table($this->table)->where("status", "=", "waiting")->whereRaw("last_modified_time + delay <= ?", $nowTime)->lock(true)->first();
        if(is_null($taskInfo)){
            Db::connection($this->connection)->rollBack();
            return null;
        }
        $taskInfo->status = "running";
        try{
            Db::connection($this->connection)->table($this->table)->where("id", "=", $taskInfo->id)->update((array)$taskInfo);
            Db::connection($this->connection)->commit();
            return $taskInfo->id;
        }catch (\Throwable $exception){
            Db::connection($this->connection)->rollBack();
            return null;
        }
    }

    public function finish($id, $data)
    {
        Db::connection($this->connection)->beginTransaction();
        $taskInfo = Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->where("status", "=", "running")->lock(true)->first();
        if(is_null($taskInfo)){
            Db::connection($this->connection)->rollBack();
            return false;
        }
        $taskInfo->status = "finished";
        $taskInfo->result = $data;
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

    public function fail($id, $stopTrying = false)
    {
        Db::connection($this->connection)->beginTransaction();
        $taskInfo = Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->where("status", "=", "running")->lock(true)->first();
        if(is_null($taskInfo)){
            Db::connection($this->connection)->rollBack();
            return false;
        }
        $nowTime = time();
        if(!$stopTrying && $taskInfo->tried_times < $taskInfo->max_try_times-1) {
            $taskInfo->status = "waiting";
            $taskInfo->delay = 0;
            $taskInfo->tried_times = is_null($taskInfo->tried_times) ? 1 : ($taskInfo->tried_times + 1);
            $taskInfo->last_modified_time = $nowTime;
            try {
                Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->update((array)$taskInfo);
                Db::connection($this->connection)->commit();
                return true;
            } catch (\Throwable $exception) {
                Db::connection($this->connection)->rollBack();
                return false;
            }
        }
        $taskInfo->status = "failed";
        $taskInfo->delay = 0;
        $taskInfo->last_modified_time = $nowTime;
        try{
            Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->update((array)$taskInfo);
            Db::connection($this->connection)->commit();
            return true;
        }catch (\Throwable $exception){
            Db::connection($this->connection)->rollBack();
            return false;
        }
    }

    public function check($id)
    {
        try{
            $taskInfo = Db::connection($this->connection)->table($this->table)->where("id", "=", $id)->first();
            return $taskInfo;
        }catch (\Throwable $exception){
            return null;
        }
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