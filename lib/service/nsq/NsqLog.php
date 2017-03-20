<?php

/**
 * Class NsqLog
 * 重写nsq客户端类日志
 *
 * @datetime : 2017/3/20 21:53
 * @author   : lihs
 * @copyright: ec
 */

namespace lib\service\nsq;


use lib\framework\nsq\Logger\LoggerInterface;
use lib\framework\log\Log;

class NsqLog implements LoggerInterface
{
    protected $logPath;

    public function __construct($logPath)
    {
        $this->logPath = $logPath;
    }

    /**
     * Log error
     *
     * @param string|\Exception $msg
     */
    public function error($msg)
    {
        return Log::error($msg, [], $this->logPath);
    }

    /**
     * Log warn
     *
     * @param string|\Exception $msg
     */
    public function warn($msg)
    {
        return Log::warning($msg, [], $this->logPath);
    }

    /**
     * Log info
     *
     * @param string|\Exception $msg
     */
    public function info($msg)
    {
        return Log::info($msg, [], $this->logPath);
    }

    /**
     * Log debug
     *
     * @param string|\Exception $msg
     */
    public function debug($msg)
    {
        return Log::debug($msg, [], $this->logPath);
    }
}