<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\log;


interface Driver
{

    /**
     * Logger Driver Init Interface method
     *
     * @param $config
     * @return mixed
     */
    public function init($config);

    /**
     * Logger Driver Write Interface method
     *
     * @param $body
     * @return mixed
     */
    public function write($body);
}