$(function(){
	replaceWord("input[name='username']","请输入用户名");
	replaceWord("input[name='email']","请输入邮箱");
	replaceWord("input[name='year']","年");
	replaceWord("input[name='day']","月");
	replaceWord("input[name='password']","请输入密码");
	replaceWord("input[name='password_2']","请确认密码");
	replaceWord("input[name='code']","请输入验证码");

	/*表单验证*/
	$("form").submit(function(){
		var username=$("input[name='username']");
		var email=$("input[name='email']");
		
		var password=$("input[name='password']");
		var password_2=$("input[name='password_2']");
		
		var code=$("input[name='code']");
		var uniqid=$("input[name='uniqid']");

		//检查用户名。
		if(username.val().length<minName||username.val().length>maxName) {
			inputError(username, $("#login-area .error-info"), "用户名不得小于" + minName + "位，不得多于" + maxName + "位");
			return false;
		}

		//检查密码。
		if(password.val()!=password_2.val()) {
			inputError(password_2, $("#login-area .error-info"), "两次输入密码不匹配");
			return false;
		}

		if(password.val().length<minPas||password.val().length>maxName) {
			inputError(password_2, $("#login-area .error-info"), "密码不得小于" + minName + "位，不得多于" + maxName + "位");
			return false;
		}

		//验证邮箱格式！
		if(/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(email.val())!=true) {
			inputError(email, $("#login-area .error-info"), "邮箱格式不符合规范");
			return false;
		}
		
		var returnVal=false;//为了让内部的决定影响到表单提交而做的一个变量。
		
		//这里会不会有机器提交，占用系统资源的漏洞。
		$.ajax({
			url  : checkUrl,
			type : "post",
			dataType:"json",
			async:false,
			date :{
				username 	: username.val(),
				email 	 	: username.val(),
				
				password 	: password.val(),
				password_2  : password_2.val(),
				
				code		: code.val(),
				uniqid		: uniqid.val()
			},
			success:function(data){
				if(!data.status)//如果状态是否
					inputError(username,$("#login-area .error-info"),data.info);
				else
					returnVal=true;
			},
			error:function(xml,statusText){
				returnVal=true;//校验失败，直接提交。
			}
		});
		return returnVal;
	});
	$(".input-warp .image-code img").click(function(){
		$(this).attr("src",function(e,oldVal){return oldVal+"?r="+Math.random()});
	});
})
