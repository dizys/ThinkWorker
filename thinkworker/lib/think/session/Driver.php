<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/10
 * Time: 14:58
 */

namespace think\session;


interface Driver
{
    public function init($config);
    public function startSession();
    public function closeSession();
    public function set($key, $value);
    public function get($key=null);
    public function has($key);
    public function delete($key);
    public function clear();
}