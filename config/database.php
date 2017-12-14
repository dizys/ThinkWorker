<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */


/**
 *  Database Settings
 */
return [
    'default'=>[
        'driver'    => 'mysql',
        'host' => 'localhost',
        'database'  => 'mvctest',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ],
    'redis' => [
        'client' => 'predis',

        'clusters' => [
            'default' => [
                [
                    'host' => 'localhost',
                    'password' => 'root',
                    'port' => 6379,
                    'database' => 0,
                ],
            ],
        ],
    ]
];