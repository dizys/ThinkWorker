<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\log;


class FileDriver implements Driver
{
    public function init($config)
    {
        // TODO: Implement init() method.
    }

    public function write($body)
    {
        // TODO: Implement write() method.
        $nowTime = time();
        $path = LOG_PATH.date("Ym", $nowTime);
        $filename = date("d", $nowTime).".log";
        if(!is_dir($path)){
            mkdir($path);
        }
        return (file_put_contents($path.DS.$filename, $body, FILE_APPEND)!=false);
    }
}