<?php
/**
 * nsq 消费服务统一启动入口
 *
 * Created by li hua.
 * User: m
 * Date: 2018/6/19
 * Time: 23:47
 */
namespace App\Console\Server;


use NsqClient\lib\NsqClient;
use NsqClient\lib\client\Client;

class NsqServer
{
    protected $serverName;

    public function __construct( $name )
    {
        $this->serverName = $name;
    }

    /**
     * 启动服务
     *
     * @throws \Exception
     */
    public function start()
    {
        $handelClass = 'App\\Console\\Server\\Service\\' . ucfirst($this->serverName) . 'NsqService';
        if ( !class_exists($handelClass) ) {
            throw new \Exception('找不到处理入口');
        }
        $handel = new $handelClass();
        $topic = config('nsq.nsqd.' . $this->serverName . '.topic', 'web');
        $channel = config('nsq.nsqd.' . $this->serverName . '.channel', 'web');
        $address = config('nsq.nsqd.' . $this->serverName . '.tcp_host', '127.0.0.1:4150');
        $log = new NsqLog();
        $client = new Client(
            $topic,
            $channel,
            '',
            $handel,
            true,
            $log
        );
        $nsqClient = new NsqClient();
        $nsqClient->init($client, $address);
    }
}