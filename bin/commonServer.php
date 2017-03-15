<?php

/**
 *
 * Date: 17-1-11
 * Time: 下午10:31
 * author :李华 yehong0000@163.com
 */
use lib\service\nsq\common\Server;
class Service
{
    private $Server;//服务对象
    private $Route;//路由对象
    public function __construct()
    {
        $this->Server=new swoole_server("127.0.0.1", 9501, SWOOLE_BASE, SWOOLE_SOCK_TCP);
        $this->Server->set([
            'worker_num' => 4,
            'daemonize' => false,
            'backlog' => 128
        ]);
        $this->Server->on('connect',[$this,'onConnect']);
        $this->Server->on('receive',[$this,'onReceive']);
        $this->Server->on('close',[$this,'onClose']);
        $this->Server->on('workerStart',[$this,'onWorkerStart']);
        $this->Server->on('workerError',[$this,'onWorkerError']);
    }

    /**
     * 连接事件
     * @param $Server
     * @param $fd
     */
    public function onConnect(\swoole_server $Server,$fd,$from_id) {
        echo '连接';
    }

    /**
     * 收到数据
     * @param swoole_server $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function onReceive(\swoole_server $server, $fd, $from_id, $data){
        $server->send($fd,$data);
    }

    /**
     * 连接关闭
     * @param swoole_server $Server
     * @param $fd
     * @param from_id $
     */
    public function onClose(\swoole_server $Server,$fd,$from_id){
        echo '关闭';
    }

    /**
     * master 启动
     * @param swoole_server $server
     * @param $worker_id
     */
    public function onWorkerStart(\swoole_server $server, $worker_id){

    }

    /**
     * master 异常退出
     * @param swoole_server $serv
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     */
    public function onWorkerError(swoole_server $serv, $worker_id, $worker_pid, $exit_code){

    }

    /**
     * 启动服务
     */
    public function start(){
        $this->Server->start();
    }
}
$service=new server();
