<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/19 0019
 * Time: 下午 1:00
 */
/**
 * 合并comment，如果用无限极分类的方法的话，需要把所有pid对应的评论全部读取出来，然后内存中合并，这样可能比较耗费资源，但用单重分类的话，数据库请求的次数变多了
 * @param $arr
 * @param int $rid
 * @param int $time
 * @return array|bool
 */
function merge_comment($arr,$rid=0,$time=0){
    if($time>3)
        return false;

    $db=M('user');
    $db_comment=M('plan_comment');

    $data=array();
    foreach($arr as $key => $value){
        if($value['rid']==$rid){
            $value['user']=$db->field('id,username,face')->find($value['uid']);
            $child=$db_comment->where("rid='%d'",$value['id'])->order('time ASC')->select();//我觉着这里用顺序比较好，用倒序在某些情况下可能会看得莫名其妙。
            foreach($child as $k=>$val){
                $child[$k]['user']=$db->field('id,username,face')->find($val['uid']);
            }
            $value['child']=$child;
            $data[]=$value;
        }
    }
    return $data;
}