<?php
namespace lib\framework\main;

use lib\framework\exception\systemException;

/**
 *系统配置缓处理类
 *
 * Date: 17-3-15
 * Time: 下午11:10
 * author :李华 yehong0000@163.com
 */
class Config
{
    //配置缓存
    private          $config;
    private          $configFile;
    protected static $objArr;

    private function __construct($fileName)
    {
        $this->configFile = $fileName . '.ini';
    }

    /**
     * 获取实例
     *
     * @param string $fileName
     *
     * @return Config
     */
    public static function getInstance($fileName = 'main')
    {
        if (!isset(self::$objArr[$fileName])) {
            self::$objArr[$fileName] = new self($fileName);
        }
        return self::$objArr[$fileName];
    }

    /**
     * 获取配置文件内容
     *
     * @return array
     * @throws systemException
     */
    private function getConfig()
    {
        if (!isset($this->config)) {
            $file = CONF_PATH . $this->configFile;
            if (!is_file($file)) {
                throw new systemException('找不到配置文件' . $this->configFile, 8010);
            }
            if (!is_writable($file)) {
                throw new systemException('无法读取相应配置文件' . $this->configFile . '，请检查', 8011);
            }
            $this->config = parse_ini_file($file,true);
            if ($this->config === false) {
                $this->config = [];
                throw new systemException('配置文件' . $this->configFile . '解析失败', 8012);
            }
        }
        return $this->config;
    }

    public function get($field = '')
    {

    }

    /**
     * 获取所有配置
     *
     * @return array
     * @throws systemException
     */
    public function getConfigs()
    {
        return $this->getConfig();
    }
}