<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


use think\exception\ClosureException;
use think\exception\ControllerException;
use think\exception\ControllerNotFoundException;
use think\exception\DbException;
use think\exception\MethodNotFoundException;
use think\exception\SyntaxParseException;

class Dispatcher
{
    protected static $deny_app_list = [];
    public static function _init(){
        $deny_app_list = config("think.deny_app_list");
        self::$deny_app_list = is_null($deny_app_list)?[]:$deny_app_list;
    }

    public static function dispatch($controller, $req, Response $resp){
        if($controller instanceof \Closure){
            $controllerRet = self::toClosure($controller, $req, $resp);
            if($controllerRet === false){
                throw new ClosureException(null, "unknown");
            }
        }else{
            $controllerRet = self::toController($controller, $req, $resp);
            if($controllerRet === false){
                throw new ControllerException(null, $controller, null);
            }
        }
        if(is_array($controllerRet) || is_object($controllerRet)){
            $encoder = config("think.default_return_array_encoder")?:"json";
            switch ($encoder){
                case "json":
                    $resp->json($controllerRet);
                    break;
                case "jsonp":
                    $resp->jsonp($controllerRet);
                    break;
                case "xml";
                    $resp->xml($controllerRet);
                    break;
            }
        }else{
            $resp->send($controllerRet);
        }
        return true;
    }

    private static function toClosure($closure, Request $req, Response $resp){
        if($closure instanceof \Closure){
            global $TW_ENV_LANG;
            $TW_ENV_LANG = $req->getLang();
            try {
                return $closure($req, $resp);
            }catch (\Error $e){
                throw new ClosureException($e);
            }catch (\PDOException $e){
                throw new DbException($e);
            }catch (\Exception $e){
                throw new ClosureException($e);
            }
        }else{
            return false;
        }
    }

    private static function toController($controller, Request $req, Response $resp){
        $c = think_controller_analyze($controller);
        $appNameSpace = $c->appNameSpace;
        $controllerNameSpace = $c->controllerNameSpace;
        if(in_array($appNameSpace, self::$deny_app_list)){
            throw new ControllerNotFoundException(null, $appNameSpace."/".$controllerNameSpace);
        }
        $methodName = $c->methodName;
        $classFullName = $c->classFullName;
        try{
            global $TW_ENV_REQUEST, $TW_ENV_LANG;
            $req->controllerInfo = $c;
            $TW_ENV_REQUEST = $req;
            $TW_ENV_LANG = $req->getLang();
            $controller = new $classFullName($req, $resp);
        }catch (\Error $e){
            $phpFile = Loader::classToAppFilePathPsr0($classFullName);
            if(!is_file($phpFile)){
                $phpFile = Loader::classToAppFilePath($classFullName);
            }
            if(is_file($phpFile)){
                $errorMsg = "";
                $result = Debug::checkPHPSyntax($phpFile, $errorMsg);
                if(!$result){
                    throw new SyntaxParseException($phpFile, $errorMsg);
                }
            }
            throw new ControllerNotFoundException($e, $appNameSpace."/".$controllerNameSpace);
        }
        if(!is_callable(array($controller, $methodName))){
            throw new MethodNotFoundException(null, $appNameSpace."/".$controllerNameSpace, $methodName);
        }
        try{
            if(is_callable(array($controller, "_init"))){
                $controller->_init();
            }
            $controllerRet = $controller->$methodName($req, $resp);
            return $controllerRet;
        }catch (\Error $e){
            throw new ControllerException($e, $appNameSpace."/".$controllerNameSpace, $methodName);
        }catch (\PDOException $e){
            throw new DbException($e, $appNameSpace."/".$controllerNameSpace, $methodName);
        }catch (\Exception $e){
            throw new ControllerException($e, $appNameSpace."/".$controllerNameSpace, $methodName);
        }
        return false;
    }
}