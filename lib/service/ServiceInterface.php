<?php
/**
 *
 * Date: 17-3-18
 * Time: 下午10:33
 * author :李华 yehong0000@163.com
 */

namespace lib\service;

use \swoole_server;

interface ServiceInterface
{
    /**
     * server 启动回调
     *
     * @param swoole_server $server
     *
     * @return mixed
     */
    public function onStart(swoole_server $server);

    /**
     * server结束回调
     *
     * @param swoole_server $server
     *
     * @return mixed
     */
    public function onShutdown(swoole_server $server);

    /**
     * worker task 启动回调
     *
     * @param swoole_server $server
     * @param int $worker_id
     *
     * @return mixed
     */
    public function onWorkerStart(swoole_server $server, $worker_id);

    /**
     * worker 结束回调
     *
     * @param swoole_server $server
     * @param int $worker_id
     *
     * @return mixed
     */
    public function onWorkerStop(swoole_server $server, $worker_id);

    /**
     * 定时器回调
     *
     * @param swoole_server $server
     * @param int $interval
     *
     * @return mixed
     */
    public function onTimer(swoole_server $server, $interval);

    /**
     * 连接进入回调 发生在woker进程
     *
     * @param swoole_server $server
     * @param int $fd
     * @param int $from_id
     *
     * @return mixed
     */
    public function onConnect(swoole_server $server, $fd, $from_id);

    /**
     * 收到数据时触发 发生在woker中
     *
     * @param swoole_server $server
     * @param int $fd
     * @param int $from_id
     * @param string $data
     *
     * @return mixed
     */
    public function onReceive(swoole_server $server, $fd, $from_id, $data);

    /**
     *接收到UDP数据包时回调此函数，发生在worker进程中
     *
     * @param swoole_server $server
     * @param string $data
     * @param array $client_info
     *
     * @return mixed
     */
    public function onPacket(swoole_server $server, $data, array $client_info);

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     *
     * @param swoole_server $server
     * @param int $fd
     * @param int $reactorId
     *
     * @return mixed
     */
    public function onClose(swoole_server $server, $fd, $reactorId);

    /**
     * task 回调
     *
     * @param swoole_server $serv
     * @param int $task_id
     * @param int $src_worker_id
     * @param string $data
     *
     * @return mixed
     */
    public function onTask(swoole_server $server, $task_id, $src_worker_id, $data);

    /**
     * task 结束时调用可以向worker发送数据
     *
     * @param swoole_server $server
     * @param $task_id
     * @param $data
     *
     * @return mixed
     */
    public function onFinish(swoole_server $server, $task_id, $data);

    /**
     * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage事件。worker/task进程都可能会触发onPipeMessage事件
     *
     * @param swoole_server $server
     * @param int $from_worker_id
     * @param string $message
     *
     * @return mixed
     */
    public function onPipeMessage(swoole_server $server, $from_worker_id, $message);

    /**
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数
     *
     * @param swoole_server $server
     * @param int $worker_id
     * @param int $worker_pid
     * @param int $exit_code
     * @param int $signal
     *
     * @return mixed
     */
    public function onWorkerError(swoole_server $server, $worker_id, $worker_pid, $exit_code, $signal);

    /**
     * 管理进程启动回调
     *
     * @param swoole_server $server
     *
     * @return mixed
     */
    public function onManagerStart(swoole_server $server);
}