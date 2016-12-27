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
/*表单验证出错时为表单添加样式，并提示信息！*/
function inputError(inputObj,errorObj,info,addClass){
	addClass=typeof addClass == "undefined"?"input-red":addClass;//默认是input-red。
	$(inputObj).addClass(addClass);
	$(errorObj).text(info);
}
