<?php

/**
 * Class DB
 * 数据库桥接
 *
 * @datetime: 2017/5/5 19:23
 * @author: lihs
 * @copyright: ec
 */

namespace lib\framework\db;

use Illuminate\Database\Capsule\Manager as Capsule;
use lib\framework\main\Config;

class DB {
    private static $config = [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'demo',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ];

    private static $DB;

    /**
     * 获取实例
     *
     * @return Capsule
     */
    public static function getInstance() {
        if (!isset(self::$DB)) {
            self::$DB=self::init();
        }
        return self::$DB;
    }

    /**
     * 初始化
     *
     * @return Capsule
     */
    public static function init() {
        $config = Config::getInstance('main')->get('database');
        self::$config = array_merge(self::config, $config);
        $capsule = new Capsule;
        // 创建链接
        $capsule->addConnection(self::$config);
        // 设置全局静态可访问
        $capsule->setAsGlobal();
        // 启动Eloquent
        $capsule->bootEloquent();
        return $capsule;
    }
}