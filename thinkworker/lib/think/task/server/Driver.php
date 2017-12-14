<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\task\server;


interface Driver
{
    public function init($config);
    public function getNextTask();
    public function finish($id, $data);
    public function fail($id, $stopTrying = false);
    public function check($id);
    public function getWaitingCount();
    public function getRunningCount();
    public function getFinishedCount();
    public function getFailedCount();
}