<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;
use think\exception\HttpException;
use think\exception\UnknownException;
use Workerman\Protocols\Http;
use Workerman\Worker;
use think\exception\FatalException;

class Server
{
    public static $worker;
    public static $onWorkerStart = null;
    private static $configs = [
        'listen_ip' => '0.0.0.0',
        'listen_port' => 80,
        'name' => 'ThinkWorker',
        'count' => 4,
        'ssl' => false,
        'ssl_local_cert'  => '/etc/nginx/conf.d/ssl/server.pem',
        'ssl_local_pk'    => '/etc/nginx/conf.d/ssl/server.key',
        'ssl_verify_peer' => false
    ];

    public static function _init($configs){
        //Basic Worker config
        !isset($configs['listen_ip']) or self::$configs['listen_ip'] = $configs['listen_ip'];
        !isset($configs['listen_port']) or self::$configs['listen_port'] = $configs['listen_port'];
        !isset($configs['name']) or self::$configs['name'] = $configs['name'];
        !isset($configs['count']) or self::$configs['count'] = $configs['count'];
        !isset($configs['ssl']) or self::$configs['ssl'] = $configs['ssl'];
        !isset($configs['ssl_local_cert']) or self::$configs['ssl_local_cert'] = $configs['ssl_local_cert'];
        !isset($configs['ssl_local_pk']) or self::$configs['ssl_local_pk'] = $configs['ssl_local_pk'];
        !isset($configs['ssl_verify_peer']) or self::$configs['ssl_verify_peer'] = $configs['ssl_verify_peer'];

        //SSL Settings
        $content = array(
            'ssl' => array(
                'local_cert'  => self::$configs['ssl_local_cert'],
                'local_pk'    => self::$configs['ssl_local_pk'],
                'verify_peer' => self::$configs['ssl_verify_peer'],
            )
        );
        //Setting up an Http protocol supported Worker with or without SSL
        if(self::$configs['ssl']){
            self::$worker = new Worker("http://".self::$configs['listen_ip'].":".self::$configs['listen_port'], $content);
            self::$worker->transport = 'ssl';
        }else{
            self::$worker = new Worker("http://".self::$configs['listen_ip'].":".self::$configs['listen_port']);
        }
        self::$worker->name = self::$configs['name'];
        self::$worker->count = self::$configs['count'];

        //Event Hooking
        self::$worker->onWorkerStart = function(){self::onWorkerStart();};
        self::$worker->onMessage = function($connection, $data){self::onMessage($connection, $data);};
    }

    private static function onWorkerStart(){
        Db::_init_by_worker_process(Config::get(null, "database"));
        /** Bootstrap App File */
        if(is_file(APP_PATH . "app.php")){
            require_once APP_PATH . "app.php";
        }
        if(!is_null(self::$onWorkerStart) && is_callable(self::$onWorkerStart)){
            (self::$onWorkerStart)();
        }
    }

    private static function onMessage($connection, $data){
        global $TW_ENV_REQUEST, $TW_ENV_RESPONSE;
        //Session auto start
        if(config("session.auto_start")){
            Http::sessionStart();
        }
        //Init Request and Response Objects
        $req = new Request($data);
        $resp = new Response($connection, $req);
        $TW_ENV_REQUEST = $req;
        $TW_ENV_RESPONSE = $resp;
        try{
            //Static files dispatching
            if(StaticDispatcher::dispatch($req, $resp)){
                return;
            };
            //Routing
            $routingResult = Route::match($req);
            $req->payload($routingResult['payload']);
            //Dispatching
            Dispatcher::dispatch($routingResult['controller'], $req, $resp);
        }catch (HttpException $e){
            //Caught HttpException then deliver msg to browser client
            $resp->setHeader("HTTP", true, $e->getStatusCode());
            $resp->send($e->getHttpBody());
            $eDesc = describeException($e);
            Log::e($eDesc, "HttpException");
        }catch (FatalException $e){
            //Caught FatalException then log error and shut down server
            $eDesc = describeException($e);
            Log::e($eDesc, "FatalException");
        }catch (\Exception $e){
            //Unknown but not Fatal Exception
            $ne = new UnknownException($e);
            $resp->setHeader("HTTP", true, $ne->getStatusCode());
            $resp->send($ne->getHttpBody());
            $eDesc = describeException($e);
            Log::e($eDesc, "UnkownException");
        }
    }

    public static function run(){
        Worker::runAll();
    }

    public static function stopAll(){
        Worker::stopAll();
    }
}