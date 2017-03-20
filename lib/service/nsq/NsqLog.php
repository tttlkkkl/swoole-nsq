<?php

/**
 * Class NsqLog
 * 重写nsq客户端类日志
 *
 * @datetime: 2017/3/20 21:53
 * @author: lihs
 * @copyright: ec
 */

namespace lib\service\nsq;


use lib\framework\nsq\Logger\LoggerInterface;

class NsqLog implements LoggerInterface {
    protected $logPath;
    protected $serverName;

    public function __construct($logPath, $serverName) {
        $this->logPath = $serverName;
        $this->serverName = $serverName;
    }

    /**
     * Log error
     *
     * @param string|\Exception $msg
     */
    public function error($msg) {
    }

    /**
     * Log warn
     *
     * @param string|\Exception $msg
     */
    public function warn($msg) {
    }

    /**
     * Log info
     *
     * @param string|\Exception $msg
     */
    public function info($msg) {
    }

    /**
     * Log debug
     *
     * @param string|\Exception $msg
     */
    public function debug($msg) {
    }
}