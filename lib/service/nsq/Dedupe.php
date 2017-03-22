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
use lib\framework\nsq\Dedupe\DedupeInterface;
use lib\framework\nsq\Message\MessageInterface;

class Dedupe implements DedupeInterface
{
//删除占位符
    const DELETED = 'D';

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
     * 加入记录
     *
     * @param string $topic
     * @param string $channel
     * @param MessageInterface $msg
     *
     * @return mixed
     */
    public function containsAndAdd($topic, $channel, MessageInterface $msg)
    {
        $hashed = $this->hash($topic, $channel, $msg);
        $this->Redis->setEx($hashed['mcKey'], $this->expire, $hashed['content']);
        return $hashed['seen'];
    }


    /**
     * 擦除记录
     *
     * @param string $topic
     * @param string $channel
     * @param MessageInterface $msg
     */
    public function erase($topic, $channel, MessageInterface $msg)
    {
        $hashed = $this->hash($topic, $channel, $msg);
        if ($hashed['seen']) {
            $this->memcached->set($hashed['mcKey'], self::DELETED);
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
        $element = "$topic:$channel:" . $msg->getPayload();
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