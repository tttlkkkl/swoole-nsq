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
    /**
     * 最大任务排队数,超过这个直，所有收到的消息将会被直接重新排队，终止投递
     *
     * @var int
     */
    private $taskQueueNum;

    public function __construct(SwooleServer $SwooleServer,$taskQueueNum=100)
    {
        $this->SwooleServer = $SwooleServer;
    }

    /**
     * @param MessageInterface $message
     */
    public function handle(MessageInterface $message)
    {
        if ($this->SwooleServer->stats()['task_queue_num'] > $this->taskQueueNum){
            //如果这里返回错误或者抛出异常将会让消息重立即新排队
            return false;
        }else{
            $this->SwooleServer->task(['id'=>$message->getId(),'msg'=>$message->getMsg()]);
            return true;
        }
    }
}