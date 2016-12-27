<?php
	/*这里是viewed-chart的数据源。*/
	$arr=array(
		array(
			'name' => '完成任务',
			'data' => array(
				array(strtotime('2016-11-17')*1000,1),
				array(strtotime('2016-11-18')*1000,3),
				array(strtotime('2016-11-19')*1000,5),
				array(strtotime('2016-11-20')*1000,2),
				array(strtotime('2016-11-21')*1000,2),
				array(strtotime('2016-11-22')*1000,4),
				array(strtotime('2016-11-23')*1000,3),
				array(strtotime('2016-11-24')*1000,3),
			)
		),
		array(
			'name' => '未完成任务',
			'data' => array(
				array(strtotime('2016-11-17')*1000,1),
				array(strtotime('2016-11-18')*1000,0),
				array(strtotime('2016-11-19')*1000,0),
				array(strtotime('2016-11-20')*1000,1),
				array(strtotime('2016-11-21')*1000,1),
				array(strtotime('2016-11-22')*1000,0),
				array(strtotime('2016-11-23')*1000,2),
				array(strtotime('2016-11-24')*1000,0),
			)
		),
	);
	echo json_encode($arr);
?>