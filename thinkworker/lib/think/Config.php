<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


class Config
{
    private static $config = [];
    public static function _init(){
        foreach(glob(CONF_PATH.'*'.CONF_EXT) as $configFile)
        {
            $lastDsPos = strrpos($configFile, DS);
            $rangeName = substr($configFile, $lastDsPos + 1);
            $rangeName = strtolower(rtrim($rangeName, CONF_EXT));
            self::$config[$rangeName] = include($configFile);
        }
    }

    public static function set($name, $value, $range = "general"){
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                self::$config[$range][strtolower($name)] = $value;
            } else {
                $name  = explode('.', $name, 2);
                self::$config[$range][strtolower($name[0])][$name[1]] = $value;
            }
            return;
        } elseif (is_array($name)) {
            if (!empty($value)) {
                self::$config[$range][$value] = isset(self::$config[$range][$value]) ?
                    array_merge(self::$config[$range][$value], $name) : $name;
                return self::$config[$range][$value];
            } else {
                return self::$config[$range] = array_merge(self::$config[$range], array_change_key_case($name));
            }
        } else {
            return self::$config[$range];
        }
    }

    public static function get($name, $range = "general"){
        if ((empty($name)||is_null($name))&& isset(self::$config[$range])) {
            return self::$config[$range];
        }
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            return isset(self::$config[$range][$name]) ? self::$config[$range][$name] : null;
        } else {
            $name    = explode('.', $name, 2);
            $name[0] = strtolower($name[0]);
            return isset(self::$config[$range][$name[0]][$name[1]]) ? self::$config[$range][$name[0]][$name[1]] : null;
        }
    }

    public static function has($name, $range = "general"){
        if (!strpos($name, '.')) {
            return isset(self::$config[$range][strtolower($name)]);
        } else {
            $name = explode('.', $name, 2);
            return isset(self::$config[$range][strtolower($name[0])][$name[1]]);
        }
    }

}