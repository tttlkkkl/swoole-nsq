<?php
namespace lib\service;
/**
 * 服务公共继承类
 *
 * Date: 17-3-15
 * Time: 下午11:52
 * author :李华 yehong0000@163.com
 */
use lib\framework\main\Config;

class Service
{
    public function __construct()
    {
        $config=Config::getInstance()->getConfigs();
        print_r($config);
    }
}