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
    /**
     * @var array
     */
    protected static $psr4Map = [];

    /**
     * Autoload Hook
     *
     * @param string $class
     * @return bool
     */
    public static function autoload($class){
        $class = ltrim($class, '\\');

        /* Try Mapping */
        if(self::loadMapping($class)){
            return true;
        }

        /* Try ThinkWorker core files */
        if(substr($class, 0, 6) == "think\\" && self::loadcore($class)){
            return true;
        }

        /* Try app files */
        $appRootNameSpace = Config::get("think.app_namespace");
        $appRootNameSpace = is_null($appRootNameSpace)?"app":$appRootNameSpace;
        if(substr($class, 0, strlen($appRootNameSpace)+1) == $appRootNameSpace."\\" && self::loadapp($class)){
            return true;
        }

        /* Try extent files */
        if(self::loadextent($class)){//In A Psr0 Way!
            return true;
        }

        return false;
    }

    /**
     * Add namespace=>path mapping relationship, one or multiple
     *
     * @param string|array $nameSpace
     * @param string|null $path
     * @return bool
     */
    public static function addMap($nameSpace, $path = null){
        if(is_array($nameSpace)){
            foreach ($nameSpace as $key => $value){
                self::addOneMap($key, $value);
            }
            return true;
        }else if(is_string($path) && is_string($nameSpace)){
            self::addOneMap($nameSpace, $path);
            return true;
        }
        return false;
    }

    /**
     * Add single one mapping relationship
     *
     * @param string $nameSpace
     * @param string $path
     * @return bool
     */
    private static function addOneMap($nameSpace, $path){
        if(0 === think_core_strrposBack($path, EXT)){
            $path = fix_slashes_in_path($path);
            self::$psr4Map[$nameSpace] = $path;
            return true;
        }
        $nameSpace = rtrim(trim($nameSpace), "\\")."\\";
        $path = rtrim(fix_slashes_in_path($path), DS).DS;
        self::$psr4Map[$nameSpace] = $path;
        return true;
    }

    /**
     * Remove mapping relationship, one or multiple
     *
     * @param string|array $nameSpace
     * @return bool
     */
    public static function rmMap($nameSpace){
        if(is_array($nameSpace)){
            foreach ($nameSpace as $name){
                if(isset(self::$psr4Map[$name])){
                    unset(self::$psr4Map[$name]);
                }
            }
            return true;
        }else if(is_string($nameSpace)){
            if(isset(self::$psr4Map[$nameSpace])){
                unset(self::$psr4Map[$nameSpace]);
                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    /**
     * Load ThinkWorker core class file
     *
     * @param string $coreClass
     * @return bool
     */
    private static function loadcore($coreClass){
        $fileName = self::classToCoreFilePath($coreClass);
        __require_file($fileName);
        return class_exists($coreClass);
    }

    /**
     * Load app class file
     *
     * @param string $appClass
     * @return bool
     */
    private static function loadapp($appClass){
        $fileName = self::classToAppFilePathPsr0($appClass);
        if(!is_file($fileName)){
            $fileName = self::classToAppFilePath($appClass);
        }
        __include_file($fileName);
        return class_exists($appClass);
    }

    /**
     * Load class file added in map
     *
     * @param string $mapClass
     * @return bool
     */
    private static function loadMapping($mapClass){
        /* File mapping */
        if(isset(self::$psr4Map[$mapClass])){
            __include_file(self::$psr4Map[$mapClass]);
            return class_exists($mapClass);
        }
        /* Directory mapping */
        foreach (self::$psr4Map as $mapKey => $mapVal){
            $mapKey = rtrim($mapKey, "\\")."\\";
            $find = strpos($mapClass, $mapKey);
            if($find===0){
                $leftNs = substr($mapClass, strlen($mapKey));
                $fileName = self::classToMappingPath($leftNs, $mapVal);
                if(!is_file($fileName)){
                    $fileName = self::classToMappingPathPsr0($leftNs, $mapVal);
                }

                if(is_file($fileName)){
                    __include_file($fileName);
                    return class_exists($mapClass);
                }
            }
        }
        return false;
    }

    /**
     * Load extent class file
     *
     * @param string $extentClass
     * @return bool
     */
    private static function loadextent($extentClass){
        $fileName = self::classToExtentPath($extentClass);
        __include_file($fileName);
        return class_exists($extentClass);
    }

    /**
     * Convert core class name to real file path
     *
     * @param string $coreClass
     * @return string
     */
    public static function classToCoreFilePath($coreClass){
        $lastNsPos = strrpos($coreClass, '\\');
        $namespace = substr($coreClass, 0, $lastNsPos);
        $className = substr($coreClass, $lastNsPos + 1);
        $fileName  = LIB_PATH.str_replace('\\', DS, $namespace) . DS;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        return $fileName;
    }

    /**
     * Convert app class name to real file path (Psr0)
     *
     * @param string $appClass
     * @return string
     */
    public static function classToAppFilePathPsr0($appClass){
        $appRootNameSpace = Config::get("think.app_namespace");
        $appRootNameSpace = is_null($appRootNameSpace)?"app":$appRootNameSpace;
        $appClass = substr($appClass, strlen($appRootNameSpace)+1);
        $lastNsPos = strrpos($appClass, '\\');
        $namespace = substr($appClass, 0, $lastNsPos);
        $className = substr($appClass, $lastNsPos + 1);
        $fileName  = APP_PATH.str_replace('\\', DS, $namespace) . DS;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        return $fileName;
    }

    /**
     * Convert app class name to real file path
     *
     * @param string $appClass
     * @return string
     */
    public static function classToAppFilePath($appClass){
        $appRootNameSpace = Config::get("think.app_namespace");
        $appRootNameSpace = is_null($appRootNameSpace)?"app":$appRootNameSpace;
        $appClass = substr($appClass, strlen($appRootNameSpace)+1);
        $lastNsPos = strrpos($appClass, '\\');
        $namespace = substr($appClass, 0, $lastNsPos);
        $className = substr($appClass, $lastNsPos + 1);
        $fileName  = APP_PATH.str_replace('\\', DS, $namespace) . DS;
        $fileName .= $className.EXT;
        return $fileName;
    }

    /**
     * Convert mapped class name to real file path (psr0)
     *
     * @param string $prs4Class
     * @param string $dirpath
     * @return string
     */
    public static function classToMappingPathPsr0($prs4Class, $dirpath){
        $lastNsPos = strrpos($prs4Class, '\\');
        $namespace = "";
        if($lastNsPos===false){
            $className = $prs4Class;
            $fileName  = $dirpath;
        }else{
            $namespace = substr($prs4Class, 0, $lastNsPos);
            $className = substr($prs4Class, $lastNsPos + 1);
            $fileName  = $dirpath.str_replace('\\', DS, $namespace) . DS;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        return $fileName;
    }

    /**
     * Convert mapped class name to real file path
     *
     * @param string $prs4Class
     * @param string $dirpath
     * @return string
     */
    public static function classToMappingPath($prs4Class, $dirpath){
        $lastNsPos = strrpos($prs4Class, '\\');
        $namespace = "";
        if($lastNsPos===false){
            $className = $prs4Class;
            $fileName  = $dirpath;
        }else{
            $namespace = substr($prs4Class, 0, $lastNsPos);
            $className = substr($prs4Class, $lastNsPos + 1);
            $fileName  = $dirpath.str_replace('\\', DS, $namespace) . DS;
        }
        $fileName .= $className. EXT;
        return $fileName;
    }

    /**
     * Convert extent class name to real file path
     *
     * @param string $extentClass
     * @param string $dirpath
     * @return string
     */
    public static function classToExtentPath($extentClass){
        $lastNsPos = strrpos($extentClass, '\\');
        $namespace = substr($extentClass, 0, $lastNsPos);
        $className = substr($extentClass, $lastNsPos + 1);
        $fileName  = EXTEND_PATH.str_replace('\\', DS, $namespace) . DS;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . EXT;
        return $fileName;
    }

    /**
     * Register the autoload hook
     *
     * @param bool $autoload
     * @return void
     */
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
