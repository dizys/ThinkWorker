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
    protected $load_app = false;

    public function __construct()
    {
        if($this->available){
            $this->_before_init();
            $this->worker = new Worker($this->socket, $this->context);
            $this->worker->name = $this->name;
            $this->worker->count = $this->process_num;
            $this->worker->onWorkerStart = function($worker){
                if($this->load_db){
                    Db::_init_by_worker_process(Config::get(null, "database"));
                }
                if($this->load_app && is_file(APP_PATH . "app.php")){
                    require_once APP_PATH . "app.php";
                }
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
    public function _before_init(){}

    public function _init(){}

    /**
     * @param Worker $worker
     */
    public function onWorkerStart($worker){}

    /**
     * @param Worker $worker
     */
    public function onWorkerReload($worker){}

    /**
     * @param Worker $worker
     */
    public function onWorkerStop($worker){}

    /**
     * @param TcpConnection $connection
     */
    public function onConnect($connection){}

    /**
     * @param TcpConnection $connection
     * @param mixed $data
     */
    public function onMessage($connection, $data){}

    /**
     * @param TcpConnection $connection
     */
    public function onClose($connection){}

    /**
     * @param TcpConnection $connection
     */
    public function onError($connection){}

    /**
     * @param TcpConnection $connection
     */
    public function onBufferFull($connection){}

    /**
     * @param TcpConnection $connection
     */
    public function onBufferDrain($connection){}
}