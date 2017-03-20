<?php
namespace lib\service\nsq;

use lib\framework\main\Config;
use lib\framework\nsq\Dedupe\OppositeOfBloomFilter;
use lib\framework\nsq\RequeueStrategy\DelaysList;
use lib\service\ServerInterface;
use lib\service\Service;
use Swoole\Server;

use lib\framework\nsq\Nsq;
use lib\framework\nsq\Lookup\Nsqlookupd;

/**
 * Class Main
 * 类功能
 *
 * @datetime: 2017/3/16 14:36
 * @author: lihs
 * @copyright: ec
 */
class Main extends Service implements ServerInterface {

    protected $NsqConfig;
    protected $logPath;
    public function __construct($serverName) {
        parent::serverInit($serverName, $this);
        $this->setConfig($serverName);
    }

    public function subscribe() {
        $lookUpd = new Nsqlookupd($this->NsqConfig->get('host') ?: '127.0.0.1:4151');
        $nsq=new Nsq($lookUpd);
        $nsqLog=new NsqLog($this->logPath);
        //消息去重规则
        $dedupe=new OppositeOfBloomFilter();
    }

    /**
     * @param $serverName
     */
    private function setConfig($serverName){
        $this->NsqConfig=Config::getInstance('nsq');
        $this->NsqConfig->setBaseKey($serverName);
        $this->logPath=$this->NsqConfig->get('logPath')?:$serverName;
    }
    /**
     * 定时器回调
     *
     * @param Server $server
     * @param int $interval
     *
     * @return mixed
     */
    public function onTimer(Server $server, $interval) {
    }

    /**
     * 连接进入回调 发生在woker进程
     *
     * @param Server $server
     * @param int $fd
     * @param int $from_id
     *
     * @return mixed
     */
    public function onConnect(Server $server, $fd, $from_id) {
    }

    /**
     * 收到数据时触发 发生在woker中
     *
     * @param Server $server
     * @param int $fd
     * @param int $from_id
     * @param string $data
     *
     * @return mixed
     */
    public function onReceive(Server $server, $fd, $from_id, $data) {
    }

    /**
     *接收到UDP数据包时回调此函数，发生在worker进程中
     *
     * @param Server $server
     * @param string $data
     * @param array $client_info
     *
     * @return mixed
     */
    public function onPacket(Server $server, $data, array $client_info) {
    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     *
     * @param Server $server
     * @param int $fd
     * @param int $reactorId
     *
     * @return mixed
     */
    public function onClose(Server $server, $fd, $reactorId) {
    }

    /**
     * task 回调
     *
     * @param Server $serv
     * @param int $task_id
     * @param int $src_worker_id
     * @param string $data
     *
     * @return mixed
     */
    public function onTask(Server $server, $task_id, $src_worker_id, $data) {
    }

    /**
     * task 结束时调用可以向worker发送数据
     *
     * @param Server $server
     * @param $task_id
     * @param $data
     *
     * @return mixed
     */
    public function onFinish(Server $server, $task_id, $data) {
    }

    /**
     * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage事件。worker/task进程都可能会触发onPipeMessage事件
     *
     * @param Server $server
     * @param int $from_worker_id
     * @param string $message
     *
     * @return mixed
     */
    public function onPipeMessage(Server $server, $from_worker_id, $message) {
    }
}