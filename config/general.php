<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

return [

    /**
     *  Workerman Engine Settings
     */
    'worker_engine'=>[
        'listen_ip' => '0.0.0.0',
        'listen_port' => 80,
        'name' => 'ThinkWorker',
        'count' => 4,
        'ssl' => false,
        'ssl_local_cert'  => '/etc/nginx/conf.d/ssl/server.pem',
        'ssl_local_pk'    => '/etc/nginx/conf.d/ssl/server.key',
        'ssl_verify_peer' => false,

        'max_request_restart' => true,
        'max_request_limit' => 1000,
    ],

    /**
     *  ThinkWorker Basic Settings
     */
    'think' => [
        'debug' => true,
        'tracing_max_lines' => false,
        'default_return_type' => 'html',
        'routing_cache_default' => true,
        'routing_cache_size' => 1000,
        'default_filter' => '',

        'app_namespace' => 'app',
        'deny_app_list' => ['common'],
        'default_app' => 'index',
        'default_controller' => 'Index',
        'default_action' => 'index',
        'default_lang' => 'zh-cn',
        'auto_lang' => true,
        'var_lang' => '_lang',

        'default_return_array_encoder'    => 'json',
        'jsonp_handler_setting_var'      => 'callback',
        'default_jsonp_handler'  => 'jsonpReturn',
        'xml_root_node' => 'think',
        'xml_root_attr' => '',
        'xml_item_node' => 'item',
        'xml_item_key'  => 'id',

        'enable_servers' => true,
    ],

    /**
     *  Template Engine Settings
     */
    'template' => [
        'engine' => 'smarty',
        'tpl_ext' => 'html',
        'caching' => true,
        'cache_lifetime' => 0,
        'debugging' => true,
        'left_delimiter' => '{',
        'right_delimiter' => '}',
        'function_cacheable' => true,
        'allow_php_tag' => true,
        'debugging_ctrl' => 'URL',
        'view_replace_str'  =>  [
            '__PUBLIC__' => '/',
        ]
    ],

    /**
     *  Cookie Settings
     */
    'cookie' => [
        'prefix' => '',
        'expire' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => ''
    ],

    /**
     *  Session Settings
     */
    'session' => [
        'driver' => 'file',
        'auto_start' => true,
        'prefix' => ''
    ],

    /**
     *  Log Settings
     */
    'log' => [
        'driver' => 'file',
        'log_path' => LOG_PATH
    ],

    /**
     *  Task Queue System Settings
     */
    'task' => [
        'enable' => true,
        'server_driver' => 'db',
        'client_driver' => 'db',
        'port' => 2073,
        'process_num' => 10,
        'default_max_try_times' => 4,
        'check_interval' => 1,
        'show_server_status' => true,
        'status_refresh_interval' => 1,

        'db_connection' => 'default',
        'db_table' => 'tasks',

    ]
];