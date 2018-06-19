<?php
/**
 *类作用说明
 *
 * Created by li hua.
 * User: m
 * Date: 2018/6/19
 * Time: 23:50
 */

namespace App\Console\Server\Service;


use NsqClient\lib\handle\HandleInterface;
use NsqClient\lib\message\MessageInterface;

class FileNsqService implements HandleInterface
{
    public function __construct()
    {
        //设置启动的最大携程数量
        \Swoole\Coroutine::set(array(
            'max_coroutine' => 100,
        ));
    }

    /**
     * 文件处理
     *
     * @param MessageInterface $message
     * @param \Closure|NULL $finish
     * @return bool|mixed
     */
    public function handle( MessageInterface $message, \Closure $finish = NULL )
    {
        //将任务投递到 swoole 协程
        $co = \Swoole\Coroutine::create(function () use ( $message, $finish ) {
            var_dump($message);
        });
        //协程创建失败 -- 如果回调函数可用说名不是自动完成模式 需要手动调用回调函数排队消息
        if ( $co === false && $finish != NULL ) {
            $finish(false);
        }
        return $co ? true : false;
    }
}