<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */
namespace think;


class Session
{
    protected static $driver = null;
    protected static $sessionPrefix;
    public static function _init($configs){
        $driverName = (isset($configs['driver']) && !empty($configs['driver']))?$configs['driver']:"file";
        self::$driver = think_core_new_driver("think\\session", $driverName);
        self::$driver->init($configs);
        self::$sessionPrefix = is_null(config("session.prefix"))?'':trim(config("session.prefix"));
    }

    public static function startSession(){
        if(self::$driver){
            return self::$driver->startSession();
        }
        return false;
    }

    public static function closeSession(){
        if(self::$driver){
            return self::$driver->closeSession();
        }
        return false;
    }

    public static function set($key, $value = null){
        if(self::$driver){
            if(is_null_or_empty(self::$sessionPrefix)){
                $ret = self::$driver->set($key, $value);
            }else{
                $ret = self::$driver->set(self::$sessionPrefix.$key, $value);
            }
            self::freshRequestSession();
            return $ret;
        }
        return false;
    }

    public static function get($key=null){
        if(self::$driver){
            $sessionPrefixLen = strlen(self::$sessionPrefix);
            if(is_null($key)){
                $ret = self::$driver->get();
                if($sessionPrefixLen === 0){
                    return $ret;
                }
                foreach ($ret as $name => $value){
                    if(substr($name, 0, $sessionPrefixLen) == self::$sessionPrefix){
                        unset($ret[$name]);
                        $name = filter(substr($name, $sessionPrefixLen));
                        $ret[$name] = $value;
                    }
                }
                return $ret;
            }else{
                if(is_null_or_empty(self::$sessionPrefix)){
                    return self::$driver->get($key);
                }
                return self::$driver->get(self::$sessionPrefix.$key);
            }
        }
        return null;
    }

    public static function has($key){
        if(self::$driver){
            if(is_null_or_empty(self::$sessionPrefix)) {
                return self::$driver->has($key);
            }else{
                return self::$driver->has(self::$sessionPrefix.$key);
            }
        }
        return null;
    }

    public static function delete($key){
        if(self::$driver){
            if(is_null_or_empty(self::$sessionPrefix)) {
                $ret = self::$driver->delete($key);
            }else{
                $ret = self::$driver->delete(self::$sessionPrefix.$key);
            }
            self::freshRequestSession();
            return $ret;
        }
        return false;
    }

    public static function clear(){
        if(self::$driver){
            $ret = self::$driver->clear();
            self::freshRequestSession();
            return $ret;
        }
        return false;
    }

    private static function freshRequestSession(){
        $req = envar("request");
        if($req){
            $req->freshSession();
        }
    }
}