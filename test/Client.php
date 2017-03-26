<?php

/**
 *
 * Date: 17-1-11
 * Time: 下午10:43
 * author :李华 yehong0000@163.com
 */
$opt = getopt('s:');
$s = isset($opt['s']) && $opt['s'] > 0 ? abs(intval($opt['s'])) : 5;

$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
while (1) {
    $client->send("clint send hello world\n");
    $msg['random'] = uniqid();
    $data = json_encode($msg, JSON_UNESCAPED_UNICODE);
    try {
        $result = $client->recv();
        if ($result) {
            echo '发送成功:' . $result . "\n";
        } else {
            echo '发送失败:' . $data . "\n";
        }
    } catch (\Exception $e) {
        echo '发送失败:' . $e->getMessage() . "\n";
    }
    sleep($s);
}
$client->close();