<?php

/**
 * Class Redis
 * redis连接处理类
 *
 * @datetime: 2017/3/21 11:06
 * @author: lihs
 * @copyright: ec
 */
namespace lib\framework\db\redis;

use lib\framework\exception\DbException;
use lib\framework\main\Config;

class Redis {
    protected $handler = null;
    protected static $Obj;
    protected $config = [
        'host'       => '127.0.0.1', // redis主机
        'port'       => 6379, // redis端口
        'password'   => '', // 密码
        'select'     => 0, // 操作库
        'expire'     => 3600, // 有效期(秒)
        'timeout'    => 0, // 超时时间(秒)
        'persistent' => true, // 是否长连接
    ];

    private function __construct($config) {
        $sysConfig = Config::getInstance('main')->get('redis');
        $config = is_array($sysConfig) ? array_merge($sysConfig, $config) : $config;
        $this->config = array_merge($this->config, $config);
        $this->open();
    }

    /**
     * 返回一个redis连接实例
     * @param $config
     * @return \Redis
     */
    static public function getInstance($config = []) {
        $key = $config;
        unset($config['select']);
        $key = md5('redis_' . implode('_', $key));
        if (!self::$Obj[$key]) {
            self::$Obj[$key] = new self($config);
        }
        if (isset($config['select']) && 0 != $config['select']) {
            self::$Obj[$key]->handler->select($config['select']);
        }
        return self::$Obj[$key]->handler;
    }

    /**
     * 连接
     * @return bool
     * @throws \Exception
     */
    private function open() {
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new DbException('not support:redis');
        }
        $this->handler = new \Redis;

        // 建立连接
        $func = $this->config['persistent'] ? 'pconnect' : 'connect';
        $connection = call_user_func([$this->handler, $func], $this->config['host'], $this->config['port'], $this->config['timeout']);
        if (!$connection) {
            throw new \Exception($this->handler->getLastError());
        }
        if ('' != $this->config['password']) {
            $this->handler->auth($this->config['password']);
        }
        return true;
    }
}