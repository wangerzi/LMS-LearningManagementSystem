$(function () {
	$.ajax({
		url:day_active_data_url,
		type:"post",
		dataType:"json",
		data:{
		},
		success:function(data){
			if(!data.status) {
				wq_alert('服务器开小差了，图表数据出错');
				return ;
			}
			traversal(data.info);
			$('#'+day_active_id).highcharts({
	        chart: {
	            type: 'spline'
	        },
	        title: {
	            text: ''
	        },
	        subtitle: {
	            text: '最近24小时 完成量/未完成量 曲线'
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
	        series: data.info
	    	});
		},
		error:function(xml,text){
			wq_alert('服务器开小差了，请刷新重试',text);
		}
	});
});