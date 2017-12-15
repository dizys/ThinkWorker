<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */
namespace think;


use think\session\Driver;

class Session
{
    /**
     * @var null|Driver
     */
    protected static $driver = null;
    /**
     * @var string
     */
    protected static $sessionPrefix;

    /**
     * Session initialization method
     *
     * @param array $configs
     */
    public static function _init($configs){
        $driverName = (isset($configs['driver']) && !empty($configs['driver']))?$configs['driver']:"file";
        self::$driver = think_core_new_driver("think\\session", $driverName);
        self::$driver->init($configs);
        self::$sessionPrefix = is_null(config("session.prefix"))?'':trim(config("session.prefix"));
    }

    /**
     * Start session
     *
     * @return bool
     */
    public static function startSession(){
        if(self::$driver){
            return self::$driver->startSession();
        }
        return false;
    }

    /**
     * Save and close session
     *
     * @return bool
     */
    public static function closeSession(){
        if(self::$driver){
            return self::$driver->closeSession();
        }
        return false;
    }

    /**
     * Set session value
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
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

    /**
     * Get one session value or all
     *
     * @param string|null $key
     * @return null|mixed
     */
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

    /**
     * Determine whether a session key exists
     *
     * @param string $key
     * @return bool
     */
    public static function has($key){
        if(self::$driver){
            if(is_null_or_empty(self::$sessionPrefix)) {
                return self::$driver->has($key);
            }else{
                return self::$driver->has(self::$sessionPrefix.$key);
            }
        }
        return false;
    }

    /**
     * Delete one session key
     *
     * @param string $key
     * @return bool
     */
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

    /**
     * Clear all sessions
     *
     * @return bool
     */
    public static function clear(){
        if(self::$driver){
            $ret = self::$driver->clear();
            self::freshRequestSession();
            return $ret;
        }
        return false;
    }

    /**
     * Get and then delete one session item
     *
     * @param string $key
     * @return array|null
     */
    public static function pull($key){
        if(self::$driver){
            if(is_null_or_empty(self::$sessionPrefix)) {
                $ret = self::$driver->get($key);
                self::$driver->delete($key);
            }else{
                $ret = self::$driver->get(self::$sessionPrefix.$key);
                self::$driver->delete(self::$sessionPrefix.$key);
            }
            self::freshRequestSession();
            return $ret;
        }
        return null;
    }

    /**
     * Fresh session content for request, called when session is changed
     *
     * @return void
     */
    private static function freshRequestSession(){
        $req = envar("request");
        if($req){
            $req->freshSession();
        }
    }
}