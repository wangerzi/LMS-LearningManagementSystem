/*/!*
 replaceWord:表单预览效果函数。
 * 
 * obj:操作的表单对象！
 * replace:提示的字母！
 * color:替换的颜色，默认#999*!/
function replaceWord(obj,replace,color){
	var type=$(obj).attr("type");
	$(obj).attr("placeholder",replace);
	/!*
	color=defaultValue(color,"#999");
	$(obj).css("color","#999")
	if(!$(obj).val()){//只有在对象的value初始化不存在的时候，才对其进行覆盖。
		$(obj).val(replace);
		$(obj).attr("type","text");
	}
	$(obj).focus(function(){
		if($(obj).val()==replace&&$(obj).attr("type")=="text"){
			$(obj).val("").removeAttr("style");
		}
		$(obj).attr("type",type);
	});
	$(obj).blur(function(){
		if($(obj).val()==""){
			$(obj).val(replace);
			$(obj).attr("type","text").css("color",color);
		}
		else
			$(obj).attr("type",type);
	});*!/
}
function defaultValue(name,value){
	return typeof name=="undefined"?value:name;
}
/!*表单验证出错时为表单添加样式，并提示信息！*!/
function inputError(inputObj,errorObj,info,addClass){
	addClass=typeof addClass == "undefined"?"input-red":addClass;//默认是input-red。
	$(inputObj).addClass(addClass);
	$(errorObj).text(info);
}*/
//公用函数区！
/*
 replaceWord:表单预览效果函数。
 *
 * obj:操作的表单对象！
 * replace:提示的字母！
 * color:替换的颜色，默认#999*/
function replaceWord(obj,replace,color){
	var type=$(obj).attr("type");
	$(obj).attr("placeholder",replace);
	/*
	 color=defaultValue(color,"#999");
	 $(obj).css("color","#999")
	 if(!$(obj).val()){//只有在对象的value初始化不存在的时候，才对其进行覆盖。
	 $(obj).val(replace);
	 $(obj).attr("type","text");
	 }
	 $(obj).focus(function(){
	 if($(obj).val()==replace&&$(obj).attr("type")=="text"){
	 $(obj).val("").removeAttr("style");
	 }
	 $(obj).attr("type",type);
	 });
	 $(obj).blur(function(){
	 if($(obj).val()==""){
	 $(obj).val(replace);
	 $(obj).attr("type","text").css("color",color);
	 }
	 else
	 $(obj).attr("type",type);
	 });*/
}
function defaultValue(name,value){
	return typeof name=="undefined"?value:name;
}
function traversal(obj,split){
	split=split?' ':split;
	for(name in obj){
		if(typeof obj[name]=='object') {
			console.log(name+':');
			traversal(obj[name], split + split);
		}
		else
			console.log(split+name+':'+obj[name]);
	}
}
function wq_alert(content,callback,title,status){
	status=defaultValue(status,'success');
	title=defaultValue(title,'提示');

	var isCallback=typeof callback == 'function';
	$('body').append('<div class="alert-background"></div>' +
		'<div class="alert-content row">' +
		'<div class="panel panel-'+status+' wq-alert col-md-4 col-md-offset-4" style="padding:0;">' +
		'<div class="panel-heading">' +
		'<div class="panel-title">' +
		'<span class="glyphicon glyphicon-info-sign"></span> ' +
		title +
		'<button class="close" onclick="close_alert(this)"> &times;</button>' +
		'</div>' +
		'</div>' +
		'<div class="panel-body">' +
		content +
		'</div>' +
		'<div class="panel-body">' +
		'<button class="btn btn-primary btn-block sure" onclick="close_alert(this);">确定</button>' +
		'</div>' +
		'</div>' +
		'</div>');
	var obj=$('body .wq-alert:last');
	obj.animate({marginTop:'20%',opacity:'1'},'normal');
	$(obj).find('.sure').click(function(){
		if(isCallback)
			callback();
	});
	obj.blur();
	$(window).keydown(function(e){
		if(e.which==27||e.which==13){
			obj.find('.close').click();
			return ;
		}
	});
}
/**
 *确认框，其中，title参数也可作为callback传入，之后是title和status。
 * @param content
 * @param title
 * @param status
 * @param callback
 */
function wq_confirm(content,title,status,callback){
	if(typeof title == 'function'){
		var tmp=callback;
		callback=title;
		//依次往后移
		title=status;
		status=tmp;
	}
	title = defaultValue(title, '提示');
	status=defaultValue(status,'success');

	var isCallback=typeof callback == 'function';
	$('body').append('<div class="alert-background"></div>' +
		'<div class="alert-content row">' +
		'<div class="panel panel-'+status+' wq-alert col-md-4 col-md-offset-4" style="padding:0;">' +
		'<div class="panel-heading">' +
		'<div class="panel-title">' +
		'<span class="glyphicon glyphicon-info-sign"></span> ' +
		title +
		'<button class="close" onclick="close_alert(this)"> &times;</button>' +
		'</div>' +
		'</div>' +
		'<div class="panel-body">' +
		content +
		'</div>' +
		'<div class="panel-body">' +
		'<button class="btn btn-primary col-md-5 sure">确定</button>' +
		'<span class="col-md-2"></span>' +
		'<button class="btn btn-danger col-md-5" onclick="close_alert(this)">取消</button>' +
		'</div>' +
		'</div>' +
		'</div>');
	var obj=$('body .wq-alert:last');
	obj.animate({marginTop:'20%',opacity:'1'},'normal');
	obj.find('.sure').click(function(){
		if(isCallback)
			callback();
		close_alert(this);
	});
	obj.blur();
	$(window).keydown(function(e){
		if(e.which==27){
			close_alert(obj.find('.close'));
		}
	});
}
/**
 * 关闭一个弹窗！
 * @param obj
 */
function close_alert(obj){
	$(obj).parents('.wq-alert').animate({marginTop:'0',opacity:0},'normal',function(){
		var content=$(obj).parents('.alert-content');
		content.prev('.alert-background').remove();
		content.remove();
	});
}
function cancle_back(){
	wq_confirm('确认离开此页吗？如若此做，您所做的修改将不会被保存。',function(){
		history.back();
	})
}
function hideObj(obj){
	$(obj).hide();
}
function showObj(obj){
	$(obj).show();
}
function utf8_length(str){
	var len=0;
	var i;
	for(i=0;i<str.length;i++){
		if(str.charCodeAt(i)>127||str.charCodeAt(i)==94)
			len+=2;
		else
			len+=1;
	}
	return len;
}
function scrollTo(obj,callback){
	$('html,body').animate({
		scrollTop:$(obj).offset().top-50,
	},'slow',callback);
}
/**
 * 进行ajax操作，同时防止多次操作
 * @param obj   操作的button等对象
 * @param url   申请的URL
 * @param data  传输的数据
 * @param success 成功的函数
 * @param error 失败的函数
 */
function ajaxOperation(obj,url,data,success,error){
	$(obj).attr('disabled','disabled');
	//以前的信息，操作之后写回去。
	var oldHtml=$(obj).html();
	$(obj).html(
		'<span class="glyphicon glyphicon-refresh"></span>' +
		'处理中'
	);
	$.ajax({
		url:url,
		type:'post',
		dataType:'json',
		data:data,
		success:function(data){
			$(obj).html(oldHtml);
			if(typeof success == 'function')
				success(data);
		},
		error:function(status,xml,statusText){
			if(typeof error =='function') {
				$(obj).html(oldHtml);
				error();
			}
			else {
				wq_alert(statusText + '请重试！');
				$(obj).html(oldHtml);
				$(obj).removeAttr('disabled');
			}
		}
	})
}
function say_hello(obj){
	var val = '';
	var date = new Date();
	var hours = date.getHours();
	if(hours > 6 && hours < 11)
		val = '早上好';
	else if(hours > 11 && hours < 18)
		val = '下午好';
	else
		val = '晚上好';
	$(obj).text(val);
}