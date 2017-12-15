<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

/**
 *  Redis Settings
 */
return [
    'client' => 'predis',

    'default' => [
        'host' => 'localhost',
        'password' => null,
        'port' => 6379,
        'database' => 0,
    ],
];