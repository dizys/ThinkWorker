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
        global $TW_ENV_REQUEST, $TW_ENV_RESPONSE;
        $TW_ENV_REQUEST = $req;
        $TW_ENV_RESPONSE = $resp;
        if($controller instanceof \Closure){
            $controllerRet = self::toClosure($controller, $req, $resp);
        }else{
            $controllerRet = self::toController($controller, $req, $resp);
        }
        if($controllerRet === false){
            throw new ControllerException($controller, "unkown");
        }
        if(is_array($controllerRet) || is_object($controllerRet)){
            $resp->json($controllerRet);
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
                $ne = new ClosureException();
                $ne->setOrigin($e);
                throw $ne;
            }catch (\PDOException $e){
                $ne = new DbException();
                $ne->setOrigin($e);
                throw $ne;
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
            throw new ControllerNotFoundException($appNameSpace."/".$controllerNameSpace);
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
            $phpFile = Loader::classToAppFilePath($classFullName);
            if(is_file($phpFile)){
                $errorMsg = "";
                $result = Debug::checkPHPSyntax($phpFile, $errorMsg);
                if(!$result){
                    throw new SyntaxParseException($errorMsg);
                }
            }
            $ne = new ControllerNotFoundException($appNameSpace."/".$controllerNameSpace);
            $ne->setOrigin($e);
            throw $ne;
        }
        if(!is_callable(array($controller, $methodName))){
            throw new MethodNotFoundException($appNameSpace."/".$controllerNameSpace, $methodName);
        }
        try{
            if(is_callable(array($controller, "_init"))){
                $controller->_init();
            }
            $controllerRet = $controller->$methodName($req, $resp);
            return $controllerRet;
        }catch (\Error $e){
            var_dump($e->getMessage());
            var_dump($e->getTraceAsString());
            $ne = new ControllerException($appNameSpace."/".$controllerNameSpace, $methodName);
            $ne->setOrigin($e);
            throw $ne;
        }catch (\PDOException $e){
            $ne = new DbException($appNameSpace."/".$controllerNameSpace, $methodName);
            $ne->setOrigin($e);
            throw $ne;
        }
        return false;
    }
}