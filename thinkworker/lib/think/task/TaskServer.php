<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\task;


use think\Config;
use think\Log;
use think\Server;
use think\task\server\Driver;
use Workerman\Lib\Timer;

class TaskServer extends Server
{
    protected $name="Task Server";

    protected $defaultMaxTryTimes = 4;

    protected $timerInterval = 1;

    protected $timerId = null;

    private $timer_last_time = 0;
    /**
     * @var null|Driver
     */
    protected $driver = null;
    public function _before_init()
    {
        $configs = Config::get("task");
        $this->process_num= is_null($configs["process_num"])?10:$configs["process_num"];
        $this->socket = "tcp://0.0.0.0:".(is_null($configs["port"])?2073:$configs["port"]);
        $this->defaultMaxTryTimes = is_null($configs["default_max_try_times"])?4:$configs["default_max_try_times"];
        $this->timerInterval = is_null($configs["check_interval"])?1:$configs["check_interval"];
        $driverName = isset($configs['server_driver'])?$configs['server_driver']:"file";
        $this->driver = think_core_new_driver("think\\task\\server", $driverName);
        if($this->driver){
            $this->driver->init($configs);
        }
}

    public function onWorkerStart($worker)
    {
        if($this->driver){
            $this->timerId = Timer::add($this->timerInterval, array($this, "onTimerStrike"));
        }
    }

    public function onTimerStrike(){
        if(Config::get("task.show_server_status")){
            $nowTime = time();
            $interval = Config::get("task.status_refresh_interval")?:5;
            if($nowTime - $this->timer_last_time > $interval){
                $this->timer_last_time = $nowTime;
                $this->printTaskServerStatus();
            }
        }

        $task = $this->driver->getNextTask();
        if(!is_null($task)){
            $taskInfo = $this->driver->check($task);
            if($taskInfo){
                $taskFullName = "app\\".$taskInfo->app."\\task\\".$taskInfo->task;
                try{
                    $taskIns = new $taskFullName();
                    if(is_callable(array($taskIns, "_init"))){
                        $taskIns->_init();
                    }
                    $methodName = is_null($taskInfo->method)?"fire":$taskInfo->method;
                    $result = $taskIns->$methodName($taskInfo->data, $task, $taskInfo);
                    if($result === false){
                        $this->driver->fail($task);
                    }else{
                        $this->driver->finish($task, $result);
                    }
                }catch (\Throwable $e){
                    $this->driver->fail($task);
                    $desc = describeException($e);
                    Log::e($desc, "Task Server");
                }
            }
        }
    }

    public function printTaskServerStatus(){
        echo "----------------------Task Server-------------------\n";
        echo "  Waiting Number: ".$this->driver->getWaitingCount()."\n";
        echo "  Running Number: ".$this->driver->getRunningCount()."\n";
        echo "  Finished Number: ".$this->driver->getFinishedCount()."\n";
        echo "  Failed Number: ".$this->driver->getFailedCount()."\n";
        echo "----------------------------------------------------------\n\n";
    }
}