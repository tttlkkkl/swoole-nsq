<?php

/**
 * Class dedupe
 * nsq去重策略
 *
 * @datetime : 2017/3/21 13:36
 * @author   : lihs
 * @copyright: ec
 */

namespace lib\service\nsq;


use lib\framework\db\redis\Redis;
use lib\Dedupe\DedupeInterface;
use lib\message\MessageInterface;
class Dedupe implements DedupeInterface
{
    //redis连接对象
    private $Redis;

    //hash map 长度  int
    private $size;

    //重复数据对比缓存过期时间
    private $expire;

    /**
     * Dedupe constructor.
     *
     * @param int $size
     * @param int $expire
     * @param array $config
     */
    public function __construct($size = 1000000, $expire = 432000, $config = [])
    {
        $this->size = $size;
        $this->expire = $expire;
        $this->Redis = Redis::getInstance(['select' => 10]);
    }


    /**
     * 添加消息到本地
     * @param $topic
     * @param $channel
     * @param MessageInterface $msg
     *
     * @return mixed
     */
    public function add($topic, $channel, MessageInterface $msg)
    {
        $hashed = $this->hash($topic, $channel, $msg);
        $this->Redis->setEx($hashed['mcKey'], $this->expire, $hashed['content']);
        return $hashed['seen'];
    }


    /**
     * 将消息从本地清除
     * @param $topic
     * @param $channel
     * @param MessageInterface $msg
     *
     * @return mixed
     */
    public function clear($topic, $channel, MessageInterface $msg)
    {
        $hashed = $this->hash($topic, $channel, $msg);
        if ($hashed['seen']) {
            $this->Redis->del($hashed['mcKey']);
        }
    }

    /**
     * 算哈希
     *
     * @param $topic
     * @param $channel
     * @param MessageInterface $msg
     *
     * @return array
     */
    private function hash($topic, $channel, MessageInterface $msg)
    {
        $element = "$topic:$channel:" . $msg->getMsg();
        $hash = hash('adler32', $element, TRUE);
        list(, $val) = unpack('N', $hash);
        $index = $val % $this->size;
        $content = md5($element);

        $mcKey = "nsq:message:{$this->size}:{$index}";
        $storedContentHash = $this->Redis->get($mcKey);
        $seen = $storedContentHash && $storedContentHash === $content;

        return ['index' => $index, 'content' => $content, 'seen' => $seen, 'mcKey' => $mcKey];
    }
}