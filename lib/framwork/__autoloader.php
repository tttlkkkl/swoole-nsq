<?php
/**
 *
 * Date: 17-1-11
 * Time: 下午10:35
 * author :李华 yehong0000@163.com
 */
/*
 *类库自动加载
 *
 */
function __autoloader($className){
    //echo '类名'.$className."\n";
    $dir=dirname(__DIR__).DIRECTORY_SEPARATOR.str_replace('\\','/',$className).'.php';
    //echo $dir."\n";
    require($dir);
}
spl_autoload_register("__autoloader",true);