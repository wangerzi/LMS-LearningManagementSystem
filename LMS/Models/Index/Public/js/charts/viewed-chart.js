$(function () {
	$.ajax({
		url:viewed_data_url,
		type:"post",
		dataType:"json",
		data:{
		},
		success:function(data){
			$('#'+viewed_id).highcharts({
	        chart: {
	            type: 'spline'
	        },
	        title: {
	            text: ''
	        },
	        subtitle: {
	            text: '最近24小时 访问量/浏览量 曲线'
	        },
	        xAxis: {
	            type: 'datetime',
	            dateTimeLabelFormats: { // don't display the dummy year
	                month: '%e. %b',
	                year: '%b'
	            },
	            title: {
	                text: '时间'
	            }
	        },
	        yAxis: {
	            title: {
	                text: '人次 (v)'
	            },
	            min: 0
	        },
	        tooltip: {
	            headerFormat: '<b>{series.name}</b><br>',
	            pointFormat: '{point.x:%H:%M} , {point.y:.0f} 次'
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
	        series: data
	    	});
		},
		error:function(xml,text){
			wq_alert(text+xml.responseText);
		}
	});
});