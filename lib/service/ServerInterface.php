<?php
/**
 *
 * Date: 17-3-18
 * Time: 下午10:33
 * author :李华 yehong0000@163.com
 */

namespace lib\service;

use Swoole\Server;

interface ServerInterface
{

    /**
     * 定时器回调
     *
     * @param Server $server
     * @param int $interval
     *
     * @return mixed
     */
    public function onTimer(Server $server, $interval);

    /**
     * 连接进入回调 发生在woker进程
     *
     * @param Server $server
     * @param int $fd
     * @param int $from_id
     *
     * @return mixed
     */
    public function onConnect(Server $server, $fd, $from_id);

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
    public function onReceive(Server $server, $fd, $from_id, $data);

    /**
     *接收到UDP数据包时回调此函数，发生在worker进程中
     *
     * @param Server $server
     * @param string $data
     * @param array $client_info
     *
     * @return mixed
     */
    public function onPacket(Server $server, $data, array $client_info);

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     *
     * @param Server $server
     * @param int $fd
     * @param int $reactorId
     *
     * @return mixed
     */
    public function onClose(Server $server, $fd, $reactorId);

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
    public function onTask(Server $server, $task_id, $src_worker_id, $data);

    /**
     * task 结束时调用可以向worker发送数据
     *
     * @param Server $server
     * @param $task_id
     * @param $data
     *
     * @return mixed
     */
    public function onFinish(Server $server, $task_id, $data);

    /**
     * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage事件。worker/task进程都可能会触发onPipeMessage事件
     *
     * @param Server $server
     * @param int $from_worker_id
     * @param string $message
     *
     * @return mixed
     */
    public function onPipeMessage(Server $server, $from_worker_id, $message);
}