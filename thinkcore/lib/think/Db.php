<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/1
 * Time: 16:04
 */

namespace think;


use Closure;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Processors\Processor;

class Db
{

    protected static $configs = [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'test',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ];

    public static function _init_by_worker_process($configs = null){
        global $TW_ENV_CAPSULE;
        if(!is_null($configs)){
            self::$configs = $configs;
        }
        $TW_ENV_CAPSULE = new Manager();
        $TW_ENV_CAPSULE->addConnection(self::$configs);
        $TW_ENV_CAPSULE->setAsGlobal();
        $TW_ENV_CAPSULE->bootEloquent();
    }

    /**
     * Set the query grammar to the default implementation.
     *
     * @return void
     */
    public static function useDefaultQueryGrammar()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->useDefaultQueryGrammar();
    }

    /**
     * Set the schema grammar to the default implementation.
     *
     * @return void
     */
    public static function useDefaultSchemaGrammar()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->useDefaultSchemaGrammar();
    }

    /**
     * Set the query post processor to the default implementation.
     *
     * @return void
     */
    public static function useDefaultPostProcessor()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->useDefaultPostProcessor();
    }


    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function getSchemaBuilder()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getSchemaBuilder();
    }


    /**
     * Begin a fluent query against a database table.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    public static function table($table)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->table($table);
    }

    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function query()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->query();
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @param  bool  $useReadPdo
     * @return mixed
     */
    public static function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->selectOne($query, $bindings, $useReadPdo);
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return array
     */
    public static function selectFromWriteConnection($query, $bindings = [])
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->selectFromWriteConnection($query, $bindings);
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public static function select($query, $bindings = [], $useReadPdo = true)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->select($query, $bindings, $useReadPdo);
    }

    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return \Generator
     */
    public static function cursor($query, $bindings = [], $useReadPdo = true)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->cursor($query, $bindings, $useReadPdo);
    }

    /**
     * Run an insert statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public static function insert($query, $bindings = [])
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->insert($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public static function update($query, $bindings = [])
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->update($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public static function delete($query, $bindings = [])
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->delete($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public static function statement($query, $bindings = [])
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->statement($query, $bindings);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public static function affectingStatement($query, $bindings = [])
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->affectingStatement($query, $bindings);
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string  $query
     * @return bool
     */
    public static function unprepared($query)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->unprepared($query);
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure  $callback
     * @return array
     */
    public static function pretend(Closure $callback)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->pretend($callback);
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param  \PDOStatement $statement
     * @param  array  $bindings
     * @return void
     */
    public static function bindValues($statement, $bindings)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->bindValues($statement, $bindings);
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param  array  $bindings
     * @return array
     */
    public static function prepareBindings(array $bindings)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->prepareBindings($bindings);
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @param  float|null  $time
     * @return void
     */
    public static function logQuery($query, $bindings, $time = null)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->logQuery($query, $bindings, $time);
    }

    /**
     * Reconnect to the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public static function reconnect()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->reconnect();
    }

    /**
     * Disconnect from the underlying PDO connection.
     *
     * @return void
     */
    public static function disconnect()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->disconnect();
    }

    /**
     * Get a new raw query expression.
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Expression
     */
    public static function raw($value)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->raw($value);
    }

    /**
     * Indicate if any records have been modified.
     *
     * @param  bool  $value
     * @return void
     */
    public static function recordsHaveBeenModified($value = true)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->recordsHaveBeenModified($value);
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public static function getPdo()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getPdo();
    }

    /**
     * Get the current PDO connection used for reading.
     *
     * @return \PDO
     */
    public static function getReadPdo()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getReadPdo();
    }

    /**
     * Set the PDO connection.
     *
     * @param  \PDO|\Closure|null  $pdo
     * @return Connection
     */
    public static function setPdo($pdo)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->setPdo($pdo);
    }

    /**
     * Set the PDO connection used for reading.
     *
     * @param  \PDO||\Closure|null  $pdo
     * @return Connection
     */
    public static function setReadPdo($pdo)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->setReadPdo($pdo);
    }

    /**
     * Set the reconnect instance on the connection.
     *
     * @param  callable  $reconnector
     * @return $this
     */
    public static function setReconnector(callable $reconnector)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->setReconnector($reconnector);
    }

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public static function getName()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getName();
    }

    /**
     * Get an option from the configuration options.
     *
     * @param  string|null  $option
     * @return mixed
     */
    public static function getConfig($option = null)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getConfig($option);
    }

    /**
     * Get the PDO driver name.
     *
     * @return string
     */
    public static function getDriverName()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getDriverName();
    }

    /**
     * Get the query grammar used by the connection.
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    public static function getQueryGrammar()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getQueryGrammar();
    }

    /**
     * Set the query grammar used by the connection.
     *
     * @param  \Illuminate\Database\Query\Grammars\Grammar  $grammar
     * @return void
     */
    public static function setQueryGrammar(\Illuminate\Database\Query\Grammars\Grammar $grammar)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->setQueryGrammar($grammar);
    }

    /**
     * Get the schema grammar used by the connection.
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    public static function getSchemaGrammar()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getSchemaGrammar();
    }

    /**
     * Set the schema grammar used by the connection.
     *
     * @param  \Illuminate\Database\Schema\Grammars\Grammar  $grammar
     * @return void
     */
    public static function setSchemaGrammar(\Illuminate\Database\Schema\Grammars\Grammar $grammar)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->setSchemaGrammar();
    }

    /**
     * Get the query post processor used by the connection.
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    public static function getPostProcessor()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getPostProcessor();
    }

    /**
     * Set the query post processor used by the connection.
     *
     * @param  \Illuminate\Database\Query\Processors\Processor  $processor
     * @return void
     */
    public static function setPostProcessor(Processor $processor)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->setPostProcessor($processor);
    }

    /**
     * Determine if the connection in a "dry run".
     *
     * @return bool
     */
    public static function pretending()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->pretending();
    }

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public static function getQueryLog()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getQueryLog();
    }

    /**
     * Clear the query log.
     *
     * @return void
     */
    public static function flushQueryLog()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->flushQueryLog();
    }

    /**
     * Enable the query log on the connection.
     *
     * @return void
     */
    public static function enableQueryLog()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->enableQueryLog();
    }

    /**
     * Disable the query log on the connection.
     *
     * @return void
     */
    public static function disableQueryLog()
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->disableQueryLog();
    }

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public static function logging()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->logging();
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public static function getDatabaseName()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getDatabaseName();
    }

    /**
     * Set the name of the connected database.
     *
     * @param  string  $database
     * @return string
     */
    public static function setDatabaseName($database)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->setDatabaseName($database);
    }

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public static function getTablePrefix()
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getTablePrefix();
    }

    /**
     * Set the table prefix in use by the connection.
     *
     * @param  string  $prefix
     * @return void
     */
    public static function setTablePrefix($prefix)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->setTablePrefix($prefix);
    }

    /**
     * Set the table prefix and return the grammar.
     *
     * @param  \Illuminate\Database\Grammar  $grammar
     * @return \Illuminate\Database\Grammar
     */
    public static function withTablePrefix(Grammar $grammar)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->withTablePrefix($grammar);
    }

    /**
     * Register a connection resolver.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return void
     */
    public static function resolverFor($driver, Closure $callback)
    {
        global $TW_ENV_CAPSULE;
        $TW_ENV_CAPSULE->resolverFor($driver, $callback);
    }

    /**
     * Get the connection resolver for the given driver.
     *
     * @param  string  $driver
     * @return mixed
     */
    public static function getResolver($driver)
    {
        global $TW_ENV_CAPSULE;
        return $TW_ENV_CAPSULE->getResolver($driver);
    }

}