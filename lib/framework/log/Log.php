<?php
namespace lib\framework\log;

use \SeasLog;

/**
 * 日志打印类
 *
 * Date: 17-3-20
 * Time: 上午12:14
 * author :李华 yehong0000@163.com
 */
class Log
{
    /**
     * 是否直接输出日志
     * @var
     */
    protected static $isPrintLog;

    public function __construct($module)
    {
        #SeasLog init
    }

    public function __destruct()
    {

    }

    /**
     * 设置basePath
     *
     * @param $basePath
     *
     * @return bool
     */
    static public function setBasePath($basePath)
    {
        return SeasLog::setBasePath($basePath);
    }

    /**
     * 获取basePath
     *
     * @return string
     */
    static public function getBasePath()
    {
        return SeasLog::getBasePath();
    }

    /**
     * 设置模块目录
     *
     * @param $module
     *
     * @return bool
     */
    static public function setLogger($module)
    {
        return SeasLog::setLogger($module);
    }

    /**
     * 获取最后一次设置的模块目录
     * @return string
     */
    static public function getLastLogger()
    {
        return SeasLog::getLastLogger();
    }

    /**
     * 设置DatetimeFormat配置
     *
     * @param $format
     *
     * @return bool
     */
    static public function setDatetimeFormat($format)
    {
        return SeasLog::setDatetimeFormat($format);
    }

    /**
     * 返回当前DatetimeFormat配置格式
     * @return string
     */
    static public function getDatetimeFormat()
    {
        return SeasLog::getDatetimeFormat();
    }

    /**
     * 统计所有类型（或单个类型）行数
     *
     * @param string $level
     * @param string $log_path
     * @param null $key_word
     *
     * @return array | long
     */
    static public function analyzerCount($level = 'all', $log_path = '*', $key_word = NULL)
    {
        return SeasLog::analyzerCount($level, $log_path, $key_word);
    }

    /**
     * 以数组形式，快速取出某类型log的各行详情
     *
     * @param        $level
     * @param string $log_path
     * @param null $key_word
     * @param int $start
     * @param int $limit
     * @param        $order 默认为正序 SEASLOG_DETAIL_ORDER_ASC，可选倒序 SEASLOG_DETAIL_ORDER_DESC
     *
     * @return array
     */
    static public function analyzerDetail($level = SEASLOG_INFO, $log_path = '*', $key_word = NULL, $start = 1, $limit = 20, $order = SEASLOG_DETAIL_ORDER_ASC)
    {
        return SeasLog::analyzerDetail($level, $log_path, $key_word, $start, $limit, $order);
    }

    /**
     * 获得当前日志buffer中的内容
     *
     * @return array
     */
    static public function getBuffer()
    {
        return SeasLog::getBuffer();
    }

    /**
     * 将buffer中的日志立刻刷到硬盘
     *
     * @return bool
     */
    static public function flushBuffer()
    {
        return SeasLog::flushBuffer();
    }

    /**
     * 记录debug日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function debug($message, array $content = [], $module = '')
    {
        self::printLog(SEASLOG_DEBUG, $message, $content);

        return SeasLog::debug($message, $content, $module);
    }

    /**
     * 记录info日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function info($message, array $content = [], $module = '')
    {
        self::printLog(SEASLOG_INFO, $message, $content);

        return SeasLog::info($message, $content, $module);
    }

    /**
     * 记录notice日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function notice($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_NOTICE
        self::printLog(SEASLOG_NOTICE, $message, $content);

        return SeasLog::notice($message, $content, $module);
    }

    /**
     * 记录warning日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function warning($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_WARNING
        self::printLog(SEASLOG_WARNING, $message, $content);

        return SeasLog::warning($message, $content, $module);
    }

    /**
     * 记录error日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function error($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_ERROR
        self::printLog(SEASLOG_ERROR, $message, $content);

        return SeasLog::error($message, $content, $module);
    }

    /**
     * 记录critical日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function critical($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_CRITICAL
        self::printLog(SEASLOG_CRITICAL, $message, $content);
        return SeasLog::critical($message, $content, $module);
    }

    /**
     * 记录alert日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function alert($message, array $content = [], $module = '')
    {
        #$level = SEASLOG_ALERT
        self::printLog(SEASLOG_ALERT, $message, $content);
        return SeasLog::alert($message, $content, $module);
    }

    /**
     * 记录emergency日志
     *
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function emergency($message, array $content = array(), $module = '')
    {
        #$level = SEASLOG_EMERGENCY
        self::printLog(SEASLOG_EMERGENCY, $message, $content);
        return SeasLog::emergency($message, $content, $module);
    }

    /**
     * 通用日志方法
     *
     * @param        $level
     * @param        $message
     * @param array $content
     * @param string $module
     */
    static public function log($level, $message, array $content = array(), $module = '')
    {
        self::printLog($level, $message, $content);
        return SeasLog::log($level, $message, $content, $module);
    }

    /**
     * @param int $param 1直接输出,0不输出
     */
    static public function setPrintParam($param = 1)
    {
        self::$isPrintLog = $param;
    }

    /**
     * 向终端输出日志内容
     *
     * @param $level
     * @param $message
     * @param $content
     */
    static private function printLog($level, $message, $content)
    {
        if (self::$isPrintLog) {
            $str = preg_replace_callback("/\{(.*?)\}/", function ($vs) use ($content) {
                return isset($content[$vs[1]]) ?: '';
            }, $message);
            echo $level . "\t" . $str . "\n";
        }
    }
}