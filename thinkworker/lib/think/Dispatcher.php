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
    /**
     * @var array
     */
    protected static $deny_app_list = [];

    /**
     * Dispatcher initialization
     *
     * @return void
     */
    public static function _init(){
        $deny_app_list = config("think.deny_app_list");
        self::$deny_app_list = is_null($deny_app_list)?[]:$deny_app_list;
    }

    /**
     * Dispatch a controller reference name to the real Controller or Closure
     *
     * @param string $controller
     * @param Request $req
     * @param Response $resp
     * @return bool
     * @throws ClosureException
     * @throws ControllerException
     */
    public static function dispatch($controller, Request $req, Response $resp){
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
        /*
         * Handling the result of Controller/Closure processing
         */
        if(is_array($controllerRet) || is_object($controllerRet)){// Array/Object packing
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
        }else{// Raw data
            $resp->send($controllerRet);
        }
        return true;
    }

    /**
     * Further dispatch to Closure
     *
     * @param \Closure $closure
     * @param Request $req
     * @param Response $resp
     * @return bool|mixed
     * @throws ClosureException
     * @throws DbException
     */
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

    /**
     * Further dispatch to Controller
     *
     * @param string $controller
     * @param Request $req
     * @param Response $resp
     * @return mixed
     * @throws ControllerException
     * @throws ControllerNotFoundException
     * @throws DbException
     * @throws MethodNotFoundException
     * @throws SyntaxParseException
     */
    private static function toController($controller, Request $req, Response $resp){
        $c = think_controller_analyze($controller);// Analyze controller reference name
        $appNameSpace = $c->appNameSpace;
        $controllerNameSpace = $c->controllerNameSpace;
        if(in_array($appNameSpace, self::$deny_app_list)){// Deny app list works
            throw new ControllerNotFoundException(null, $appNameSpace."/".$controllerNameSpace);
        }
        $methodName = $c->methodName;
        $classFullName = $c->classFullName;
        try{
            global $TW_ENV_REQUEST, $TW_ENV_LANG;
            $req->controllerInfo = $c; // Inject Controller environment info
            $TW_ENV_REQUEST = $req;
            $TW_ENV_LANG = $req->getLang();
            $controller = new $classFullName($req, $resp); // Get a Controller instance
        }catch (\Error $e){
            /* Locate the Controller PHP file */
            $phpFile = Loader::classToAppFilePathPsr0($classFullName);
            if(!is_file($phpFile)){
                $phpFile = Loader::classToAppFilePath($classFullName);
            }
            if(is_file($phpFile)){
                $errorMsg = "";
                $result = Debug::checkPHPSyntax($phpFile, $errorMsg);// Check syntax error
                if(!$result){
                    throw new SyntaxParseException($phpFile, $errorMsg);
                }
            }
            /* File not found */
            throw new ControllerNotFoundException($e, $appNameSpace."/".$controllerNameSpace);
        }
        if(!is_callable(array($controller, $methodName))){
            /* Method not found */
            throw new MethodNotFoundException(null, $appNameSpace."/".$controllerNameSpace, $methodName);
        }
        try{
            /* Call _init method of Controller */
            if(is_callable(array($controller, "_init"))){
                $controller->_init();
            }
            /* Call _beforeAction method of Controller */
            if(is_callable(array($controller, "_beforeAction"))){
                $controller->_beforeAction($methodName);
            }
            /* Call the big guy */
            $controllerRet = $controller->$methodName($req, $resp);
            return $controllerRet;
        }catch (\Error $e){
            throw new ControllerException($e, $appNameSpace."/".$controllerNameSpace, $methodName);
        }catch (\PDOException $e){
            throw new DbException($e, $appNameSpace."/".$controllerNameSpace, $methodName);
        }catch (\Exception $e){
            throw new ControllerException($e, $appNameSpace."/".$controllerNameSpace, $methodName);
        }
    }
}