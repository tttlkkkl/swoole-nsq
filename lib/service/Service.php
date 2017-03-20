<?php
namespace lib\service;

use lib\framework\exception\ServiceException;
use lib\framework\log\Log;
use lib\framework\main\Config;
use lib\service\HttpInterface;
use lib\service\ServerInterface;
use lib\service\WebSocketInterface;
use Swoole\Server;

/**
 * Class service
 * 服务公共继承类this->config->get('set'
 *
 * @datetime : 2017/3/16 14:40
 * @author   : lihs
 * @copyright: ec
 */
class Service {
    protected $logPath;
    protected $serverName;
    public $server;
    public $config;


    public function __construct() {

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
        $this->server = new \swoole_server(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501,
            $this->config->get('server.model') ? \constant($this->config->get('server.model')) : SWOOLE_BASE,
            $this->config->get('server.socket' ? \constant($this->config->get('server.model')) : SWOOLE_SOCK_TCP)
        );
        if (is_array($this->config->get('set'))) {
            $this->server->set($this->config->get('set'));
        }
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
        $this->server = new \swoole_server(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501
        );
        if (is_array($this->config->get('set'))) {
            $this->server->set($this->config->get('set'));
        }
        $this->setCallback(2, $this->server, $serverCallback);
    }

    /**
     * http客户端启动程序
     * @param $serverName
     * @param \lib\service\HttpInterface $serverCallback
     */
    public function httpInit($serverName, HttpInterface $serverCallback) {
        $this->setConfig($serverName);
        $this->server = new \swoole_server(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501
        );
        if (is_array($this->config->get('set'))) {
            $this->server->set($this->config->get('set'));
        }
        $this->setCallback(3, $this->server, $serverCallback);
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
     * @param ServiceInterface $serverCallback
     */
    private function setCallback($type, Server $server, ServiceInterface $serverCallback) {
        $server->on('onStart', [$this, 'onStart']);
        $server->on('onShutdown', [$this, 'onShutdown']);
        $server->on('onWorkerError', [$this, 'onWorkerError']);
        $server->on('onManagerStart', [$this, 'onManagerStart']);
        $server->on('onManagerStop', [$this, 'onManagerStop']);
        call_user_func([$this, 'onWorkerStart'], $server, $serverCallback);
        $server->on('onWorkerStop', [$serverCallback, 'onWorkerStop']);
        $server->on('onTimer', [$serverCallback, 'onTimer']);
        $server->on('onClose', [$serverCallback, 'onClose']);
        $server->on('onTask', [$serverCallback, 'onTask']);
        $server->on('onFinish', [$serverCallback, 'onFinish']);
        $server->on('onPipeMessage', [$serverCallback, 'onPipeMessage']);
        //webSocket和server额外回调
        if ($type === 1 || $type === 3) {
            $server->on('onConnect', [$serverCallback, 'onConnect']);
            $server->on('onReceive', [$serverCallback, 'onReceive']);
        }
        //webSocket 额外回调
        if ($type === 2) {
            $server->on('onOpen', [$serverCallback, 'onOpen']);
            $server->on('onMessage', [$serverCallback, 'onMessage']);
        }
        //http 额外回调
        if ($type === 3) {
            $server->on('onRequest', [$serverCallback, 'onRequest']);
        }
    }

    /**
     * 主进程启动回调，上不允许在子类中重写
     * @param Server $server
     */
    private function onStart(Server $server) {
        Log::info($this->serverName . ': 服务启动...', [], $this->logPath);
        //记录主进程pid
        Log::info('master_pid:{master_pid},manager_pid:{manager_pid}', [
            '{master_pid}'  => $server->master_pid,
            '{manager_pid}' => $server->manager_pid
        ], $this->serverName . '.pid');
        $this->cliSetProcessTitle('Master');
    }

    /**
     * 主进程结束回调，不允许子类中重写
     * @param Server $server
     */
    private function onShutdown(Server $server) {
        Log::info($this->serverName . ': 服务退出...', [], $this->logPath);
    }

    /**
     * 管理进程启动
     * @param Server $server
     * @param $worker_id
     */
    private function onManagerStart(Server $server, $worker_id) {
        Log::info($this->serverName . ': manager 启动...', [], $this->logPath);
    }

    /**
     * 管理进程停止
     * @param Server $server
     * @param $worker_id
     */
    private function onManagerStop(Server $server, $worker_id) {
        Log::info($this->serverName . ': manager 退出...', [], $this->logPath);
    }

    /**
     * 工作进程启动
     *
     * @param Server $server
     * @param $worker_id
     */
    private function onWorkerStart(Server $server, $serverCallback) {
        $server->on('onWorkerStart', function (Server $server, $worker_id) use ($serverCallback) {
            if ($worker_id >= $server->setting['worker_num']) {
                Log::info($this->serverName . ': task_worker 启动...', [], $this->logPath);
                $this->cliSetProcessTitle($this->serverName . '_task_worker');
            } else {
                Log::info($this->serverName . ': event_worker 启动...', [], $this->logPath);
                $this->cliSetProcessTitle($this->serverName . 'event_worker');
            }
            if (method_exists($serverCallback, 'onWorkerStart')) {
                call_user_func([$serverCallback, 'onWorkerStart'], $server, $worker_id);
            }
        });
    }

    /**
     * 工作进程退出
     *
     * @param Server $server
     * @param $worker_id
     */
    private function onWorkerStop(Server $server, $worker_id) {
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
    private function onWorkerError(Server $server, $worker_id, $worker_pid, $exit_code, $signal) {
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
    private function cliSetProcessTitle($title) {
        $title = $this->serverName . '_' . $title;
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($title);
        } else {
            swoole_set_process_name($title);
        }
    }
}