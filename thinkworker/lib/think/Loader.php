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

        if(self::loadextent($class)){
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

    private static function loadextent($extentClass){
        $fileName = self::classToExtentPath($extentClass);
        __include_file($fileName);
        return class_exists($extentClass);
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

    public static function classToExtentPath($extentClass){
        $lastNsPos = strrpos($extentClass, '\\');
        $namespace = substr($extentClass, 0, $lastNsPos);
        $className = substr($extentClass, $lastNsPos + 1);
        $fileName  = EXTEND_PATH.str_replace('\\', DS, $namespace) . DS;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
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
    if(is_file($file)){
        return include $file;
    }else{
        return false;
    }
}

function __require_file($file)
{
    return require $file;
}
