<?php

/**
 * 业务路由分发类
 * Date: 17-1-11
 * Time: 下午10:48
 * author :李华 yehong0000@163.com
 */

namespace lib\service\nsq\common;

use lib\framework\log\Log;

class Route {
    /**
     * 模块
     * @var
     */
    const MODEL = 'index';

    /**
     * 控制器
     * @var
     */
    const CONTROLLER = 'Index';

    /**
     * 方法
     * @var
     */
    const ACTION = 'index';


    /**
     * 路由
     *
     * @param $param
     *
     * @return mixed
     * @throws \Exception
     */
    public static function dispatcher($param) {
        $msg = isset($param['msg']) ? json_decode($param['msg'], true) : [];
        $data = isset($msg['data']) ? $msg['data'] : [];
        $target = isset($msg['target']) ? $msg['target'] : '';
        if ($target && is_string($target)) {
            $route = array_filter(explode('/', $target));
            $model = isset($route[0]) ? strtolower($route[0]) : self::MODEL;
            $controller = isset($route[1]) ? ucfirst(strtolower($route[1])) : self::CONTROLLER;
            $action = isset($route[2]) ? $route[2] : self::ACTION;
        } else {
            $model = self::MODEL;
            $controller = self::CONTROLLER;
            $action = self::ACTION;
        }
        $class = 'app\\module\\' . $model . '\\' . $controller;
        if (class_exists($class) && method_exists($class, $action)) {
            $Object = new $class;
            return call_user_func([$Object, $action], $data);
        } else {
            $err = '找不到处理程序';
            Log::critical($err . ':' . $class . '::' . $action);
            throw new \Exception($err, -1);
        }
    }
}