<?php
/**
 * 重定向nsq客户端类库日志输出到框架系统日志中
 *
 * Created by li hua.
 * User: m
 * Date: 2018/6/19
 * Time: 23:47
 */
namespace App\Console\Server;

use Illuminate\Support\Facades\Log;
use NsqClient\lib\log\LogInterface;
class NsqLog implements LogInterface
{
    /**
     * @param $msg
     */
    public function error( $msg )
    {
        Log::error($msg);
    }

    /**
     * @param $msg
     */
    public function warn( $msg )
    {
        Log::warning($msg);
    }

    /**
     * @param $msg
     */
    public function info( $msg )
    {
        Log::info($msg);
    }

    /**
     * @param $msg
     */
    public function debug( $msg )
    {
        Log::debug($msg);
    }
}