/*
* @Author: 94468
* @Date:   2017-10-23 21:04:24
* @Last Modified by:   94468
* @Last Modified time: 2017-10-26 13:28:13
*/
$(function(){
	// 获取后台已有模板信息并加载
	$.ajax({
		url 	: 	KEYWORDS_INFO_URL,
		dataType: "json", 
		success: function(data){
			if(!data.status){
				alert(data.info);
				return false;
			}
			// 获取信息
			var list = data.data;
			// 追加信息
			var origin = $('#keyword');
			var form = origin.find('form');

			for(var i = 0; i < list.length; i++){
				addKeywordsForm(form, list[i].keywords, list[i].content);
			}
		}, 
		error:function(errno, status, errstr){
			alert('获取关键字回复信息失败！'+errstr);
		}
	})
})
function addKeywordsForm(form, keywords, content){
	var origin = $(form);
	keywords = keywords || '';
	content = content || '';
	origin.append('<div class="row" class="reply-template">'+
		 '<div class="input-field col s6">'+
          '<input type="text" maxlength="200" name="keywords[]" value="'+keywords+'">'+
          '<label class="active">关键字，使用英文逗号隔开</label>'+
        '</div>'+
        '<div class="input-field col s12">'+
          '<textarea class="materialize-textarea" name="contents[]" maxlength="400">'+content+'</textarea>'+
          '<label class="active">回复内容，支持__date__,__time__,__nickname__,__sex__,关键字替换</label>'+
        '</div>'+
	'</div>');
}