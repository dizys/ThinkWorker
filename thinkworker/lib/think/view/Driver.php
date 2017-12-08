<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\view;


interface Driver
{
    public function init($config);
    public function fetch($file);
    public function assign($name, $value = null);
    public function clearAssign($name);
    public function clearAllAssign();
    public function replace($name, $value = null);
    public function outReplace($name, $value = null);
    public function config($name, $value);
    public function registerFunction($functionName, $asName = null);
    public function getInstance();
}