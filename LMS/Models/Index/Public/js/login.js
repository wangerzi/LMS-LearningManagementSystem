$(function(){
	replaceWord(("input[name='username']"),"用户名/邮箱");
	replaceWord(("input[name='password']"),"请输入密码");
	replaceWord(("input[name='verify']"),"请输入验证码");

	$(".input-warp .image-code img").click(function(){
		$(this).attr("src",function(e,oldVal){return oldVal+"?r="+Math.random()});
	});
});
