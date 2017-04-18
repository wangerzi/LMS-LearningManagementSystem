$(function () {
	var conf = {//模板配置
		chart: {
			type: 'spline'
		},
		title: {
			text: ''
		},
		subtitle: {
			text: '最近7天每日任务 完成量/未完成量 曲线'
		},
		xAxis: {
			type: 'datetime',
			dateTimeLabelFormats: { // don't display the dummy year
				month: '%e. %b',
				year: '%b'
			},
			title: {
				text: '时间(t)'
			}
		},
		yAxis: {
			title: {
				text: '任务数 (v)'
			},
			min: 0
		},
		tooltip: {
			headerFormat: '<b>{series.name}</b><br>',
			pointFormat: '{point.x:%m月%e日} , {point.y:.0f} 个'
		},

		plotOptions: {
			spline: {
				marker: {
					enabled: true
				}
			}
		},
		style:{
			color:"#FF9600",
			zIndex:"2"
		},
		colors: ['#7cb5ec', '#FF9600', '#90ed7d', '#f7a35c', '#8085e9',
			'#f15c80', '#e4d354', '#8085e8', '#8d4653', '#91e8e1'],
		series: {}//数据存放处。
	};
	$('.charts').html('<div class="center-block text-center" style="line-height:245px;"><span class="glyphicon glyphicon-refresh"></span> 数据获取中</div>');
	$.ajax({
		url:day_mission_and_plan_url,
		type:"post",
		dataType:"json",
		data:{
		},
		success:function(data){
			if(!data.status) {
				wq_alert('服务器开小差了，图表数据出错');
				return ;
			}
			conf.series=data.mission;
			$('#'+day_mission_id).highcharts(conf);
			conf.yAxis.title.text='计划数(p)';
			conf.series=data.plan;
			$('#'+day_plan_id).highcharts(conf);

			//顺便更新下主页的信息。
			$('#planNotCompleteNum').text(data['plan'][0]['data'][days-1][1]);
			$('#planCompleteNum').text(data['plan'][1]['data'][days-1][1]);
		},
		error:function(xml,text){
			wq_alert('服务器开小差了，请刷新重试',text);
		}
	});
	$.ajax({
		url:day_supervision_url,
		type:"post",
		dataType:"json",
		data:{
		},
		success:function(data){
			if(!data.status) {
				wq_alert('服务器开小差了，图表数据出错');
				return ;
			}
			conf.subtitle.text='最近7天 已处理/未处理监督 曲线';
			conf.series=data.supervision;
			$('#'+day_supervision_id).highcharts(conf);
		},
		error:function(xml,text){
			wq_alert('服务器开小差了，请刷新重试',text);
		}
	});
});