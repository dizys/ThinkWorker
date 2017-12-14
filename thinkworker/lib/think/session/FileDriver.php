<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/10
 * Time: 15:11
 */

namespace think\session;


use Workerman\Protocols\Http;

class FileDriver implements Driver
{
    public function init($config)
    {

    }

    public function startSession()
    {
        return Http::sessionStart();
    }

    public function closeSession()
    {
        return Http::sessionWriteClose();
    }

    public function set($key, $value)
    {
        if(is_string($key)){
            $_SESSION[$key] = $value;
            return true;
        }
        return false;
    }

    public function get($key=null)
    {
        if(is_null($key)){
            return $_SESSION;
        }
        return isset($_SESSION[$key])?$_SESSION[$key]:null;
    }

    public function has($key){
        return (isset($_SESSION[$key]) && !is_null($_SESSION[$key]));
    }

    public function delete($key)
    {
        if(is_array($key)){
            foreach ($key as $item){
                unset($_SESSION[$item]);
            }
            return true;
        }else{
            unset($_SESSION[$key]);
            return true;
        }
    }

    public function clear()
    {
        $_SESSION = ["placeholder"];
        return true;
    }
}