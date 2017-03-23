<?php
namespace lib\service;

use lib\framework\exception\ServiceException;
use lib\framework\log\Log;
use lib\framework\main\Config;
use lib\service\HttpInterface;
use lib\service\ServerInterface;
use lib\service\WebSocketInterface;
use Swoole\Server;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Http\Server as HttpServer;

/**
 * Class service
 * 服务公共继承类this->config->get('set'
 *
 * @datetime : 2017/3/16 14:40
 * @author   : lihs
 * @copyright: ec
 */
class Service {
    protected $serverName;
    public $server;
    public $config;
    private $logPath;
    private $daemonize;

    public function __construct() {
        if (!extension_loaded('swoole')) {
            exit(-1);
        }
        if (!extension_loaded('SeasLog')) ;
    }


    /**
     * server 公共启动处理
     *
     * @param $serverName
     * @param \lib\service\ServiceInterface $serverCallback
     *
     * @throws ServiceException
     */
    public function serverInit($serverName, ServerInterface $serverCallback) {
        $this->setConfig($serverName);
        $this->serverName = $serverName;
        $this->server = new Server(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501,
            $this->config->get('server.model') !== null ? $this->config->get('server.model') : SWOOLE_BASE,
            $this->config->get('server.socket') !== null ? $this->config->get('server.socket') : SWOOLE_SOCK_TCP
        );
        $this->serverSet();
        $this->setCallback(1, $this->server, $serverCallback);
    }


    /**
     * webSocket 服务启动处理
     *
     * @param $serverName
     * @param \lib\service\WebSocketInterface $serverCallback
     *
     * @throws ServiceException
     */
    public function webSocketInit($serverName, WebSocketInterface $serverCallback) {
        $this->setConfig($serverName);
        $this->server = new WebSocketServer(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501
        );
        $this->serverSet();
        $this->setCallback(2, $this->server, $serverCallback);
    }

    /**
     * http客户端启动程序
     * @param $serverName
     * @param \lib\service\HttpInterface $serverCallback
     */
    public function httpInit($serverName, HttpInterface $serverCallback) {
        $this->setConfig($serverName);
        $this->server = new HttpServer(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501
        );
        $this->serverSet();
        $this->setCallback(3, $this->server, $serverCallback);
        $this->server->start();
    }

    /**
     * 设置swoole运行参数
     *
     * @throws ServiceException
     */
    private function serverSet() {
        $serverSet = $this->config->get('set');
        if (is_array($serverSet)) {
            if (isset($serverSet['log_file'])) {
                $serverSet['log_file'] = LOG_PATH . $serverSet['log_file'];
                if (!file_exists($serverSet['log_file'])) {
                    $dir = substr($serverSet['log_file'], 0, strrpos($serverSet['log_file'], '/'));
                    if (!is_dir($dir)) {
                        if (!mkdir($dir, 0765, true)) {
                            throw new ServiceException('创建swoole日志目录失败', 8039);
                        }
                    }
                    if (!touch($serverSet['log_file'])) {
                        throw new ServiceException('创建swoole日志文件失败', 8040);
                    }
                } elseif (!is_writeable($serverSet['log_file'])) {
                    throw new ServiceException('swoole日志文件不可写', 8041);
                }
            }
            if (isset($serverSet['daemonize'])) {
                $this->daemonize = $serverSet['daemonize'];
                Log::setPrintParam(1);
            }
            $this->server->set($serverSet);
        }
    }

    /**
     * 设置公告配置
     *
     * @param $serverName
     *
     * @throws ServiceException
     */
    private function setConfig($serverName) {
        $this->config = Config::getInstance('swoole');
        $this->config->setBaseKey($serverName);
        $this->logPath = $this->config->get('logPath');
    }

    /**
     * 设置函数回调
     *
     * @param $type
     * @param Server $server
     * @param $serverCallback
     */
    private function setCallback($type, $server, $serverCallback) {
        $server->on('start', [$this, 'onStart']);
        $server->on('shutdown', [$this, 'onShutdown']);
        $server->on('workerError', [$this, 'onWorkerError']);
        $server->on('managerStart', [$this, 'onManagerStart']);
        $server->on('managerStop', [$this, 'onManagerStop']);
        $server->on('workerStart', [$this, 'onWorkerStart']);
        $server->on('workerStop', [$serverCallback, 'onWorkerStop']);
        $server->on('close', [$serverCallback, 'onClose']);
        $server->on('task', [$serverCallback, 'onTask']);
        $server->on('finish', [$serverCallback, 'onFinish']);
        $server->on('pipeMessage', [$serverCallback, 'onPipeMessage']);
        //webSocket和server额外回调
        if ($type === 1 || $type === 3) {
            $server->on('connect', [$serverCallback, 'onConnect']);
            $server->on('receive', [$serverCallback, 'onReceive']);
        }
        //webSocket 额外回调
        if ($type === 2) {
            $server->on('open', [$serverCallback, 'onOpen']);
            $server->on('message', [$serverCallback, 'onMessage']);
        }
        //http 额外回调
        if ($type === 3) {
            $server->on('request', [$serverCallback, 'onRequest']);
        }
    }

    /**
     * 主进程启动回调，上不允许在子类中重写
     * @param Server $server
     */
    public final function onStart(Server $server) {
        Log::info($this->serverName . ': 服务启动...', [], $this->logPath);
        //记录主进程pid
        try{
            $pidWrite=\file_put_contents(PID_PATH . $this->serverName . '.pid', [
                'master_pid'  => $server->master_pid,
                'manager_pid' => $server->manager_pid
            ]);
        }catch (ServiceException $E){
            $pidWrite=false;
        }
        if(!$pidWrite){
            Log::info($this->serverName . ': pid生成记录失败...', [], $this->logPath);
        }
        $this->cliSetProcessTitle('Master');
    }

    /**
     * 主进程结束回调，不允许子类中重写
     * @param Server $server
     */
    public final function onShutdown(Server $server) {
        Log::info($this->serverName . ': 服务退出...', [], $this->logPath);
    }

    /**
     * 管理进程启动
     * @param Server $server
     * @param $worker_id
     */
    public final function onManagerStart(Server $server, $worker_id) {
        Log::info($this->serverName . ': manager 启动...', [], $this->logPath);
        $this->cliSetProcessTitle('manager');
    }

    /**
     * 管理进程停止
     * @param Server $server
     * @param $worker_id
     */
    public final function onManagerStop(Server $server, $worker_id) {
        Log::info($this->serverName . ': manager 退出...', [], $this->logPath);
    }

    /**
     * 工作进程启动
     *
     * @param Server $server
     * @param $worker_id
     */
    public final function onWorkerStart(Server $server, $worker_id) {
        if ($worker_id >= $server->setting['worker_num']) {
            $msg = $this->serverName . ': task_worker  启动...';
            $this->cliSetProcessTitle('task_worker');
        } else {
            $msg = $this->serverName . ': event_worker 启动...';
            $this->cliSetProcessTitle('event_worker');
        }
        Log::info($msg, [], $this->logPath);

    }

    /**
     * 工作进程退出
     *
     * @param Server $server
     * @param $worker_id
     */
    public final function onWorkerStop(Server $server, $worker_id) {
        if ($worker_id >= $server->setting['worker_num']) {
            Log::info($this->serverName . ': task_worker 退出...', [], $this->logPath);
        } else {
            Log::info($this->serverName . ': event_worker 退出...', [], $this->logPath);
        }
    }

    /**
     * 工作进程异常退出
     * @param Server $server
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     * @param $signal
     */
    public final function onWorkerError(Server $server, $worker_id, $worker_pid, $exit_code, $signal) {
        Log::emergency($this->serverName . ':worker进程异常退出,worker_id:{worker_id},worker_pid:{worker_pid},exit_code:{exit_code},signal:{signal}',
            [
                '{worker_id}'  => $worker_id,
                '{worker_pid}' => $worker_pid,
                '{exit_code}'  => $exit_code,
                '{signal}'     => $signal
            ],
            $this->logPath);
    }

    /**
     * 设置进程名
     * @param string $title
     */
    public final function cliSetProcessTitle($title) {
        $title = 'php_' . $this->serverName . '_' . $title;
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        } else {
            swoole_set_process_name($title);
        }
    }
}