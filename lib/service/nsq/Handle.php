<?php
/**
 *
 * Date: 17-4-2
 * Time: 下午2:14
 * author :李华 yehong0000@163.com
 */

namespace lib\service\nsq;

use lib\handle\HandleInterface;
use lib\message\MessageInterface;
use Swoole\Server as SwooleServer;

class Handle implements HandleInterface
{
    /**
     * ｔａｓｋ任务投递
     * @var
     */
    private $SwooleServer;

    public function __construct(SwooleServer $SwooleServer)
    {
        $this->SwooleServer = $SwooleServer;
    }

    /**
     * @param MessageInterface $message
     */
    public function handle(MessageInterface $message)
    {
        $this->SwooleServer->task($message->getMsg());
        return true;
    }
}