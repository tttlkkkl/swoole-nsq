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
define('PID_PATH', ROOT . DS . 'bin' . DS . 'pid'.DS);

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
register_shutdown_function('handleFatal');
function handleFatal()
{
    $error = error_get_last();
    if (isset($error['type']))
    {
        switch ($error['type'])
        {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                $message = $error['message'];
                $file = $error['file'];
                $line = $error['line'];
                $log = "$message ($file:$line)\nStack trace:\n";
                $trace = debug_backtrace();
                foreach ($trace as $i => $t)
                {
                    if (!isset($t['file']))
                    {
                        $t['file'] = 'unknown';
                    }
                    if (!isset($t['line']))
                    {
                        $t['line'] = 0;
                    }
                    if (!isset($t['function']))
                    {
                        $t['function'] = 'unknown';
                    }
                    $log .= "#$i {$t['file']}({$t['line']}): ";
                    if (isset($t['object']) and is_object($t['object']))
                    {
                        $log .= get_class($t['object']) . '->';
                    }
                    $log .= "{$t['function']}()\n";
                }
                if (isset($_SERVER['REQUEST_URI']))
                {
                    $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
                }
                error_log($log);
                echo $log;
            default:
                break;
        }
    }
}


