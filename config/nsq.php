<?php
/**
 * nsq 配置
 */
return [
    'nsqd'   => [
        'file' => [
            'topic'   => 'wechat_file',
            'channel' => 'wechat_file',
            'http_host' => '127.0.0.1:4151',
            'tcp_host' => '127.0.0.1:4150'
        ]
    ],
    'lookup' => [],
];