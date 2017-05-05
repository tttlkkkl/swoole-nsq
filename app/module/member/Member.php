<?php

/**
 *
 * Date: 17-4-2
 * Time: 下午7:23
 * author :李华 yehong0000@163.com
 */
namespace app\module\member;
class Member
{
    protected static $Obj;

    /**
     * 获取实例
     * @return Member
     */
    public static function getInstance(){
        if(!self::$Obj){
            self::$Obj=new self();
        }
        return self::$Obj;
    }

    /**
     * @param $data
     */
    public function pullMember($data){
        print_r($data);
    }
}