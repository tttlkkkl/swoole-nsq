<?php

/**
 *
 * Date: 17-3-23
 * Time: 下午10:17
 * author :李华 yehong0000@163.com
 */
require('../lib/framework/Bootstrap.php');

class NsqClient
{
    /**
     * 循环发布nsq消息
     */
    public static function sendMessage()
    {
        $opt = getopt('s:');
        $s = isset($opt['s']) && $opt['s'] > 0 ? abs(intval($opt['s'])) : 5;
        $url = 'http://127.0.0.1:4151/pub?topic=nsq_common';
        $msg = [
            'target' => 'member/member/pullMember',
            'data'   => [
                1, 2
            ],
            'random' => 'xxx'
        ];
        while (1) {
            $msg['random'] = uniqid();
            $data = json_encode($msg, JSON_UNESCAPED_UNICODE);
            $header = '';
            try {
                $result = \lib\tool\Http::post($url, $data, $header);
                if ($result) {
                    echo '发送成功:' . $data . "\n";
                } else {
                    echo '发送失败:' . $data . "\n";
                }
            } catch (\Exception $e) {
                echo '发送失败:' . $e->getMessage() . "\n";
            }
            sleep($s);
        }
    }
}

NsqClient::sendMessage();