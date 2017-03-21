<?php
/**
 * 公共
 * Date: 17-3-16
 * Time: 上午12:01
 * author :李华 yehong0000@163.com
 */

namespace lib\service\nsq\common;


use lib\service\nsq\Main;

class Server extends Main
{
    public function __construct()
    {
        parent::__construct('nsqCommon');
    }

    public function start(){
        $this->subscribe();
    }
}