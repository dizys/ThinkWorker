<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


class Loader
{
    protected $mapCache = [];
    public static function autoload($class){
        $class = ltrim($class, '\\');
        if(substr($class, 0, 6) == "think\\" && self::loadcore($class)){
            return true;
        }

        if(substr($class, 0, 4) == "app\\" && self::loadapp($class)){
            return true;
        }

        return false;
    }

    private static function loadcore($coreClass){
        $fileName = self::classToCoreFilePath($coreClass);
        __require_file($fileName);
        return class_exists($coreClass);
    }

    private static function loadapp($appClass){
        $fileName = self::classToAppFilePath($appClass);
        __include_file($fileName);
        return class_exists($appClass);
    }

    public static function classToCoreFilePath($coreClass){
        $lastNsPos = strrpos($coreClass, '\\');
        $namespace = substr($coreClass, 0, $lastNsPos);
        $className = substr($coreClass, $lastNsPos + 1);
        $fileName  = LIB_PATH.str_replace('\\', DS, $namespace) . DS;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        return $fileName;
    }

    public static function classToAppFilePath($appClass){
        $appClass = substr($appClass, 4);
        $lastNsPos = strrpos($appClass, '\\');
        $namespace = substr($appClass, 0, $lastNsPos);
        $className = substr($appClass, $lastNsPos + 1);
        $fileName  = APP_PATH.str_replace('\\', DS, $namespace) . DS;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        return $fileName;
    }

    public static function classToIlluminateFilePath($illuminateClass){
        $illuminateClass = substr($illuminateClass, 10);
        $lastNsPos = strrpos($illuminateClass, '\\');
        if($lastNsPos!=false){
            $namespace = substr($illuminateClass, 0, $lastNsPos);
            var_dump($namespace);
            $className = substr($illuminateClass, $lastNsPos + 1);
            $fileName  = LIB_PATH."illuminate".DS.str_replace('\\', DS, $namespace) . DS;
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        }else{
            $className = $illuminateClass;
            $fileName  = LIB_PATH."illuminate". DS;
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        }
        return $fileName;
    }

    public static function register($autoload=false){
        spl_autoload_register($autoload ?: 'think\\Loader::autoload', true, true);
    }
}

/**
 * Scope Restriction
 *
 * @param $file
 * @return mixed
 */
function __include_file($file)
{
    return include $file;
}

function __require_file($file)
{
    return require $file;
}
