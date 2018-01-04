<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\route;


class SubRoute
{
    /**
     * @var array
     */
    protected $mapRules = [];

    /**
     * @var null|string
     */
    protected $anyDefault = null;

    /**
     * @var null|string
     */
    protected $suffix = null;


    protected $cache = null;
    public function __construct($anyDefault = null, $suffix = null, $cache = null)
    {
        $this->anyDefault = $anyDefault;
        $this->suffix = $suffix;
        $this->cache = $cache;
    }

    public function add($pattern, $rule = null){
        if(is_null($rule) && is_array($pattern)){
            foreach ($pattern as $key => $value){
                $this->addOne($key, $value);
            }
        }else if(is_array($pattern)){
            foreach ($pattern as $key){
                $this->addOne($key, $rule);
            }
        }else if(!is_null($rule) && is_string($pattern)){
            $this->addOne($pattern, $rule);
        }
    }

    public function any($pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        $suffix = empty($suffix)?$this->suffix:$suffix;
        $cache = empty($cache)?$this->cache:$cache;
        $this->add($pattern, [$handler, $this->anyDefault, $payloadCheck, $suffix, $cache]);
    }

    public function get($pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        $suffix = empty($suffix)?$this->suffix:$suffix;
        $cache = empty($cache)?$this->cache:$cache;
        $this->add($pattern, [$handler, 'GET', $payloadCheck, $suffix, $cache]);
    }

    public function post($pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        $suffix = empty($suffix)?$this->suffix:$suffix;
        $cache = empty($cache)?$this->cache:$cache;
        $this->add($pattern, [$handler, 'POST', $payloadCheck, $suffix, $cache]);
    }

    public function put($pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        $suffix = empty($suffix)?$this->suffix:$suffix;
        $cache = empty($cache)?$this->cache:$cache;
        $this->add($pattern, [$handler, 'PUT', $payloadCheck, $suffix, $cache]);
    }

    public function delete($pattern, $handler, $payloadCheck = null, $suffix = null, $cache = null){
        $suffix = empty($suffix)?$this->suffix:$suffix;
        $cache = empty($cache)?$this->cache:$cache;
        $this->add($pattern, [$handler, 'DELETE', $payloadCheck, $suffix, $cache]);
    }

    public function group($prefix, $rules, $configs = []){
        $realPrefix = $prefix;
        $realRules = $rules;
        $realConfigs = $configs;
        if(is_array($prefix)){
            $realConfigs = $prefix;
            $realPrefix = null;
        }
        $method = $this->anyDefault;
        $suffix = $this->suffix;
        $cache = $this->cache;
        foreach ($realConfigs as $key => $value){
            switch ($key){
                case 0:
                case "method":
                    $method = $value;
                    break;
                case 1:
                case "suffix":
                case "ext":
                    $suffix = $value;
                    break;
                case 2:
                case "cache":
                    $cache = $value;
                    break;
            }
        }
        if($realRules instanceof \Closure){
            $subRoute = new SubRoute($method, $suffix, $cache);
            $realRules($subRoute);
            $realRules = $subRoute->getRules();
        }else if(!is_array($realRules)){
            $realRules = [];
        }
        if(empty($realPrefix)){
            $this->mapRules = array_merge_recursive($this->mapRules, $realRules);
        }else{
            if(!isset($this->mapRules['['.$realPrefix.']'])){
                $this->mapRules['['.$realPrefix.']'] = [];
            }
            $this->mapRules['['.$realPrefix.']']=array_merge_recursive($this->mapRules['['.$realPrefix.']'], $realRules);
        }
    }

    public function addOne($pattern, $rule){
        if(strpos($pattern, "@")===0 && is_array($rule) && count($rule) == 5){
            $rule = [$rule[0], $rule[1], $rule[4]];
        }
        $this->mapRules[$pattern] = $rule;
    }

    public function getRules(){
        return $this->mapRules;
    }
}