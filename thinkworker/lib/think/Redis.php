<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;

use Closure;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Arr;

class Redis
{
    /**
     * @var null|RedisManager
     */
    protected static $manager = null;

    /**
     * Redis initialization method
     *
     * @param array $configs
     * @return void
     */
    public static function _init($configs){
        self::$manager = new RedisManager(Arr::pull($configs, "client", "predis"), $configs);
    }

    /**
     * Get a Redis connection by name.
     *
     * @param  string|null  $name
     * @return  null|\Illuminate\Redis\Connections\Connection
     */
    public static function connection($name = null)
    {
        if(self::$manager){
            return self::$manager->connection($name);
        }
        return null;
    }

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public static function connections()
    {
        if(self::$manager){
            return self::$manager->connections();
        }
        return [];
    }

    /**
     * Returns the value of the given key.
     *
     * @param  string  $key
     * @return string|null
     */
    public static function get($key){
        if(self::$manager){
            return self::$manager->connection()->get($key);
        }
        return null;
    }

    /**
     * Get the values of all the given keys.
     *
     * @param  array  $keys
     * @return array
     */
    public static function mget(array $keys){
        if(self::$manager){
            return self::$manager->connection()->mget($keys);
        }
        return [];
    }

    /**
     * Determine if the given keys exist.
     *
     * @param  array  ...$keys
     * @return int
     */
    public static function exists(...$keys)
    {
        if(self::$manager){
            return self::$manager->connection()->exists(...$keys);
        }
        return 0;
    }

    /**
     * Set the string value in argument as value of the key.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string|null  $expireResolution
     * @param  int|null  $expireTTL
     * @param  string|null  $flag
     * @return bool
     */
    public static function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
    {
        if(self::$manager){
            return self::$manager->connection()->set($key, $value, $expireResolution, $expireTTL, $flag);
        }
        return false;
    }

    /**
     * Set the given key if it doesn't exist.
     *
     * @param  string  $key
     * @param  string  $value
     * @return int
     */
    public static function setnx($key, $value)
    {
        if(self::$manager){
            return self::$manager->connection()->setnx($key, $value);
        }
        return 0;
    }


    /**
     * Get the value of the given hash fields.
     *
     * @param  string $key
     * @param array ...$dictionary
     * @return array
     */
    public static function hmget($key, ...$dictionary)
    {
        if(self::$manager){
            return self::$manager->connection()->hmget($key, $dictionary);
        }
        return [];
    }

    /**
     * Set the given hash fields to their respective values.
     *
     * @param  string  $key
     * @param  array  ...$dictionary
     * @return int
     */
    public static function hmset($key, ...$dictionary)
    {
        if(self::$manager){
            return self::$manager->connection()->hmset($key, $dictionary);
        }
        return 0;
    }

    /**
     * Set the given hash field if it doesn't exist.
     *
     * @param  string  $hash
     * @param  string  $key
     * @param  string  $value
     * @return int
     */
    public static function hsetnx($hash, $key, $value)
    {
        if(self::$manager){
            return self::$manager->connection()->hsetnx($hash, $key, $value);
        }
        return 0;
    }

    /**
     * Removes the first count occurrences of the value element from the list.
     *
     * @param  string  $key
     * @param  int  $count
     * @param  $value  $value
     * @return int|false
     */
    public static function lrem($key, $count, $value)
    {
        if(self::$manager){
            return self::$manager->connection()->lrem($key, $count, $value);
        }
        return false;
    }

    /**
     * Removes and returns a random element from the set value at key.
     *
     * @param  string  $key
     * @param  int|null  $count
     * @return mixed|false
     */
    public static function spop($key, $count = null)
    {
        if(self::$manager){
            return self::$manager->connection()->spop($key, $count);
        }
        return false;
    }

    /**
     * Add one or more members to a sorted set or update its score if it already exists.
     *
     * @param  string  $key
     * @param  array  $dictionary
     * @return int
     */
    public static function zadd($key, $dictionary)
    {
        if(self::$manager){
            return self::$manager->connection()->zadd($key, $dictionary);
        }
        return 0;
    }

    /**
     * Return elements with score between $min and $max.
     *
     * @param  string  $key
     * @param  mixed  $min
     * @param  mixed  $max
     * @param  array  $options
     * @return array
     */
    public static function zrangebyscore($key, $min, $max, $options = [])
    {
        if(self::$manager){
            return self::$manager->connection()->zrangebyscore($key, $min, $max, $options);
        }
        return [];
    }

    /**
     * Return elements with score between $min and $max.
     *
     * @param  string  $key
     * @param  mixed  $min
     * @param  mixed  $max
     * @param  array  $options
     * @return array
     */
    public static function zrevrangebyscore($key, $min, $max, $options = [])
    {
        if(self::$manager){
            return self::$manager->connection()->zrevrangebyscore($key, $min, $max, $options);
        }
        return [];
    }

    /**
     * Find the intersection between sets and store in a new set.
     *
     * @param  string  $output
     * @param  array  $keys
     * @param  array  $options
     * @return int
     */
    public static function zinterstore($output, $keys, $options = [])
    {
        if(self::$manager){
            return self::$manager->connection()->zinterstore($output, $keys, $options);
        }
        return 0;
    }

    /**
     * Find the union between sets and store in a new set.
     *
     * @param  string  $output
     * @param  array  $keys
     * @param  array  $options
     * @return int
     */
    public static function zunionstore($output, $keys, $options = [])
    {
        if(self::$manager){
            return self::$manager->connection()->zunionstore($output, $keys, $options);
        }
        return 0;
    }


    /**
     * Execute commands in a pipeline.
     *
     * @param  callable  $callback
     * @return \Redis|array|null
     */
    public static function pipeline(callable $callback = null)
    {
        if(self::$manager){
            return self::$manager->connection()->pipeline($callback);
        }
        return null;
    }

    /**
     * Execute commands in a transaction.
     *
     * @param  callable  $callback
     * @return \Redis|array|null
     */
    public static function transaction(callable $callback = null)
    {
        if(self::$manager){
            return self::$manager->connection()->transaction($callback);
        }
        return null;
    }

    /**
     * Evaluate a LUA script serverside, from the SHA1 hash of the script instead of the script itself.
     *
     * @param  string  $script
     * @param  int  $numkeys
     * @param  mixed  $arguments
     * @return mixed
     */
    public static function evalsha($script, $numkeys, ...$arguments)
    {
        if(self::$manager){
            return self::$manager->connection()->evalsha($script, $numkeys, ...$arguments);
        }
        return false;
    }

    /**
     * Evaluate a script and retunr its result.
     *
     * @param  string  $script
     * @param  int  $numberOfKeys
     * @param  array  ...$arguments
     * @return mixed
     */
    public static function eval($script, $numberOfKeys, ...$arguments)
    {
        if(self::$manager){
            return self::$manager->connection()->eval($script, $numberOfKeys, ...$arguments);
        }
        return false;
    }

    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @return void
     */
    public static function subscribe($channels, Closure $callback)
    {
        if(self::$manager){
            self::$manager->connection()->subscribe($channels, $callback);
        }
    }

    /**
     * Subscribe to a set of given channels with wildcards.
     *
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @return void
     */
    public static function psubscribe($channels, Closure $callback)
    {
        if(self::$manager){
            self::$manager->connection()->psubscribe($channels, $callback);
        }
    }

    /**
     * Dynamically pass all the missing calls to default connection
     *
     * @param string $name
     * @param array $arguments
     * @return null
     */
    public static function __callStatic($name, $arguments)
    {
        if(self::$manager){
            return self::$manager->connection()->$name(...$arguments);
        }
        return null;
    }


}