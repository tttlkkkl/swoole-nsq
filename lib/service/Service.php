<?php
namespace lib\service;

use lib\framework\exception\ServiceException;
use lib\framework\log\Log;
use lib\framework\main\Config;
use lib\service\ServiceInterface;

/**
 * Class service
 * 服务公共继承类this->config->get('set'
 *
 * @datetime : 2017/3/16 14:40
 * @author   : lihs
 * @copyright: ec
 */
class Service
{
    protected $logPath;
    public    $server;
    public    $config;

    public function __construct()
    {

    }


    /**
     * server 公共启动处理
     *
     * @param $serverName
     * @param \lib\service\ServiceInterface $serverCallback
     *
     * @throws ServiceException
     */
    public function serverInit($serverName,ServiceInterface $serverCallback)
    {
        $this->setConfig($serverName);
        $this->server = new \swoole_server(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501,
            $this->config->get('server.model') ? \constant($this->config->get('server.model')) : SWOOLE_BASE,
            $this->config->get('server.socket' ? \constant($this->config->get('server.model')) : SWOOLE_SOCK_TCP)
        );
        if(is_array($this->config->get('set'))){
            $this->server->set($this->config->get('set'));
        }
    }

    /**
     * websocket 服务启动处理
     *
     * @param $serverName
     * @param \lib\service\ServiceInterface $serverCallback
     *
     * @throws ServiceException
     */
    public function webSocketInit($serverName,ServiceInterface $serverCallback){
        $this->setConfig($serverName);
        $this->server = new \swoole_server(
            $this->config->get('server.ip') ?: '0.0.0.0',
            $this->config->get('server.port') ?: 9501
        );
        if(is_array($this->config->get('set'))){
            $this->server->set($this->config->get('set'));
        }
    }

    /**
     * 设置公告配置
     *
     * @param $serverName
     *
     * @throws ServiceException
     */
    protected function setConfig($serverName){
        $this->config = Config::getInstance($serverName);
        $this->config->setBaseKey($serverName);
        $this->logPath = $this->config->get('logPath');
        if (!is_dir($this) || !is_writable($this->logPath)) {
            throw  new ServiceException('日志路径错误或不可写', 1040);
        } else {
            //设置日志目录
            Log::setBasePath($this->logPath);
        }
    }
}