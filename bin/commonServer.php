<?php
$serv = new Swoole\Server("127.0.0.1", 9501);
$serv->set(array(
    'worker_num' => 8,   //工作进程数量
    'daemonize' => false, //是否作为守护进程
));
$serv->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});
$serv->on('receive', function ($serv, $fd, $from_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    echo "Client: receive.{$data}\n";
});
$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});
$serv->start();