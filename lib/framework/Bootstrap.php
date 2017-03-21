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
define('PID_PATH', ROOT . DS . 'bin' . DS . 'pid');

$MainConfig = lib\framework\main\Config::getInstance('main');
$logPath = $MainConfig->get('logPath');
if (!is_dir($logPath) || !is_writable($logPath)) {
    throw new lib\framework\exception\SystemException('日志目录' . $logPath . '错误或不可写', 8010);
}
lib\framework\log\Log::setBasePath($logPath);
define('LOG_PATH', $logPath);

if ($MainConfig->get('debug')) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 'Off');
}


