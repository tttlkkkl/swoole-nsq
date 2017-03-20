<?php
/**
 *
 * Date: 17-1-11
 * Time: 下午10:33
 * author :李华 yehong0000@163.com
 */
require('__autoloader.php');
define('ROOT', dirname(dirname(__DIR__)));
define('DS', DIRECTORY_SEPARATOR);
define('CONF_PATH', ROOT . DS . 'conf' . DS);

$logPath = lib\framework\main\Config::getInstance('main')->get('logPath');
if (!is_dir($logPath) || !is_writable($logPath)) {
    throw new lib\framework\exception\SystemException('日志目录' . $logPath . '错误或不可写', 8010);
}
lib\framework\log\Log::setBasePath($logPath);
define('LOG_PATH', $logPath);

