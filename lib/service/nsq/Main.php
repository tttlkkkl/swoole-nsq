<?php
namespace lib\service\nsq;

use lib\framework\exception\ServiceException;
use lib\framework\log\Log;
use lib\framework\main\Config;
use lib\NsqClient;
use lib\service\ServerInterface;
use lib\service\Service;
use Swoole\Server;
use lib\service\nsq\Handle;
use lib\Nsq;
use lib\lookup\Lookup;
use lib\client\Client;

/**
 * Class Main
 * 类功能
 *
 * @datetime : 2017/3/16 14:36
 * @author   : lihs
 * @copyright: ec
 */
class Main extends Service implements ServerInterface
{

    protected $NsqConfig;
    protected $logPath;
    protected $serverName;

    public function __construct($serverName)
    {
        $this->setConfig($serverName);
        $this->serverName = $serverName;
    }

    /**
     * 启动
     */
    public function start()
    {
        parent::serverInit($this->serverName, $this);
    }

    /**
     * 订阅
     *
     * @throws ServiceException
     * @throws \lib\exception\LookupException
     */
    public function init()
    {
        $topicChannel = $this->NsqConfig->get('topicChannel');
        $authSecret = $this->NsqConfig->get('authSecret');
        if (($index = strpos($topicChannel, ':'))) {
            $topic = substr($topicChannel, 0, $index);
            $channel = substr($topicChannel, $index + 1);
        } else {
            throw new ServiceException('', -1);
        }

        $lookupConfig = $this->NsqConfig->get('lookupHost') ?: ($this->NsqConfig->get('lookup.host', true) ?: '127.0.0.1:4161');
        $Lookup = new Lookup($lookupConfig);
        $nsqdList = $Lookup->lookupHosts($topic);
        if (!$nsqdList || !isset($nsqdList['lookupHosts']) || !$nsqdList['lookupHosts'] || !is_array($nsqdList['lookupHosts'])) {
            throw new ServiceException('未发现可用服务', -1);
        }
        $NsqLog = new NsqLog($this->logPath);
        //重新定义消息去重规则
        $Dedupe = new Dedupe();
        $Handel = new Handle($this->server);
        $NsqClient=new NsqClient();
        foreach ($nsqdList['lookupHosts'] as $host) {
            if (!$channel) {
                $channel = isset($nsqdList['topicChannel'][$host][0]) ? $nsqdList['topicChannel'][$host][0] : 'nsq_swoole_client';
            }
            Log::info('开始订阅:' . $host . ':' . $topicChannel);
            $Client = new Client($topic, $channel, $authSecret, $Handel, $NsqLog, $Dedupe);
            $NsqClient->init($Client, $host);
        }
    }

    /**
     * 初始订阅副服务
     *
     * @param Server $server
     * @param $worker_id
     *
     * @throws ServiceException
     */
    public function onWorkerStart(Server $server, $worker_id)
    {
        parent::onWorkerStart($server, $worker_id);
        //在ｗｏｋｅｒ进程中启动订阅服务
        if ($worker_id < $server->setting['worker_num']) {
            $this->init();
        }
    }

    /**
     * @param $serverName
     */
    private function setConfig($serverName)
    {
        $this->NsqConfig = Config::getInstance('nsq');
        $this->NsqConfig->setBaseKey($serverName);
        $this->logPath = $this->NsqConfig->get('logPath') ?: $serverName;
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
    public function onConnect(Server $server, $fd, $from_id)
    {
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
    public function onReceive(Server $server, $fd, $from_id, $data)
    {
        $server->send($fd, 'hello');
        $server->task($data);
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
    public function onPacket(Server $server, $data, array $client_info)
    {
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
    public function onClose(Server $server, $fd, $reactorId)
    {
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
    public function onTask(Server $server, $task_id, $src_worker_id, $data)
    {
        echo "收到任务投递\n";
        var_dump($data);
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
    public function onFinish(Server $server, $task_id, $data)
    {
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
    public function onPipeMessage(Server $server, $from_worker_id, $message)
    {
    }
}