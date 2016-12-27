<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/24 0024
 * Time: 下午 4:20
 */
return array(
    //最少计划名称
    'PLAN_MIN_NAME' => 2,
    //最长计划名称
    'PLAN_MAX_NAME' => 20,
    //列表页显示的计划名字长度！
    'PLAN_NAME_SHOW' => 12,

    //最小阶段名字
    'STAGE_MIN_NAME' => 2,
    'STAGE_MAX_NAME' => 20,
    'STAGE_MAX_INFO' => 200,
    //最小任务名字
    'MISSION_MIN_NAME' => 2,
    'MISSION_MAX_NAME' => 20,
    'MISSION_MAX_INFO' => 200,

    //任务默认完成时间
    'MISSION_DEFAULT_TIME' => 6,
    //任务最大完成时间
    'MISSION_MAX_TIME'     => 48,

    //同一计划每小时最多完成任务数目
    'PLAN_MISSION_COMPLETE_TIME_HOUR'=> 2,
    //计划默认位置
    'PLAN_DEFAULT_FACE'    => __ROOT__.APP_PATH.'data/plan/defaultFace.jpg',
    //计划图片图片添加水印
    //是否开启
    'PLAN_WATER_OPEN'    => false,
    //地址
    'PLAN_WATER_ADDR'    => APP_PATH.'data/plan/water.png',
);
?>