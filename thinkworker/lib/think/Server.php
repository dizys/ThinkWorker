<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/10
 * Time: 22:34
 */

namespace think;


use Workerman\Connection\TcpConnection;
use Workerman\Worker;

abstract class Server
{
    /**
     * @var string
     */
    protected $name = "untitled";

    /**
     * @var string
     */
    protected $socket = "";

    /**
     * @var array
     */
    protected $context = [];

    /**
     * @var bool
     */
    protected $available = true;

    /**
     * @var int
     */
    protected $process_num = 1;

    /**
     * @var null|Worker
     */
    protected $worker = null;

    /**
     * @var bool
     */
    protected $load_db = true;

    /**
     * @var bool
     */
    protected $load_redis = true;

    /**
     * @var bool
     */
    protected $load_session = true;

    /**
     * @var bool
     */
    protected $load_app = false;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        if($this->available){
            $this->_before_init();
            $this->worker = new Worker($this->socket, $this->context);
            $this->worker->name = $this->name;
            $this->worker->count = $this->process_num;
            $this->worker->onWorkerStart = function($worker){
                \think\server\Loader::loadEssentials($this);
                $this->onWorkerStart($worker);
            };
            $this->worker->onWorkerReload = array($this, "onWorkerReload");
            $this->worker->onWorkerStop = array($this, "onWorkerStop");
            $this->worker->onConnect = array($this, "onConnect");
            $this->worker->onMessage = array($this, "onMessage");
            $this->worker->onClose = array($this, "onClose");
            $this->worker->onError = array($this, "onError");
            $this->worker->onBufferFull = array($this, "onBufferFull");
            $this->worker->onBufferDrain = array($this, "onBufferDrain");
            $this->_init();
        }
    }

    /**
     * Server _before_init method, called before `new Worker`
     *
     * @return void
     */
    public function _before_init(){}

    /**
     * Server initialization method, called after `new Worker`
     *
     * @return void
     */
    public function _init(){}

    /**
     * Worker onWorkerStart callback
     *
     * @param Worker $worker
     */
    public function onWorkerStart($worker){}

    /**
     * Worker onWorkerReload callback
     *
     * @param Worker $worker
     */
    public function onWorkerReload($worker){}

    /**
     * Worker onWorkerStop callback
     *
     * @param Worker $worker
     */
    public function onWorkerStop($worker){}

    /**
     * Worker onConnect callback
     *
     * @param TcpConnection $connection
     */
    public function onConnect($connection){}

    /**
     * Worker onMessage callback
     *
     * @param TcpConnection $connection
     * @param mixed $data
     */
    public function onMessage($connection, $data){}

    /**
     * Worker onClose callback
     *
     * @param TcpConnection $connection
     */
    public function onClose($connection){}

    /**
     * Worker onError callback
     *
     * @param TcpConnection $connection
     */
    public function onError($connection){}

    /**
     * Worker onBufferFull callback
     *
     * @param TcpConnection $connection
     */
    public function onBufferFull($connection){}

    /**
     * Worker onBufferDrain callback
     *
     * @param TcpConnection $connection
     */
    public function onBufferDrain($connection){}
}