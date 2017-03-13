<?php

/**
 *
 * Date: 17-1-11
 * Time: 下午10:43
 * author :李华 yehong0000@163.com
 */
$client = new swoole_client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();