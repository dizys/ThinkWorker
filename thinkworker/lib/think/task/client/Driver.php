<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\task\client;


 interface Driver
{
    public function init($config);
    public function push($app, $task, $method, $data, $maxTryTimes, $priority, $delay);
    public function edit($id, $app, $task, $method, $data, $maxTryTimes);
    public function cancel($id);
    public function shift($id, $delay);
    public function check($id);
    public function getWaitingCount();
    public function getRunningCount();
    public function getFinishedCount();
    public function getFailedCount();
}