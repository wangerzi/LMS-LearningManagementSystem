/**
 * Created by Administrator on 2017/1/30.
 */
$(function () {
    $("#code").click(function(){
        $(this).attr("src",function(e,oldVal){return oldVal+"&r="+Math.random()});
    });
    replaceWord($('input[name="username"]'),'用户名/邮箱');
    //相比内部的修改，还需要加上一个验证码
    var validate={
        validators:{
            notEmpty:{
                message:'密匙不能为空'
            },
            stringLength:{
                min:CODE_LEN,
                max:CODE_LEN,
                message:'密匙长度需要是'+CODE_LEN+'字',
            },
            remote:{
                url:codeCheckUrl,
                message:'密匙不正确或密匙已过期',
                delay: 2000,
                data:function(){
                    return {
                        code:$("input[name='code']").val(),
                        name:$("input[name='username']").val(),
                    };
                },
                type:'post',
            }
        }
    };
    $('#passwordEdit').bootstrapValidator('addField','code',validate);
    var validate={
        validators:{
            notEmpty:{
                message:'用户名不能为空'
            },
            regexp:{
                regexp: /^([\u4E00-\u9FFFa-zA-z1-9_]*)|([a-zA-z1-9]{1,20}@{a-zA-Z1-9}{1,20}[\.a-zA-Z1-9]{0,5})$/,
                message: '用户名只能是中文、英文、数字、下划线，或者邮箱不符合规范'
            },
            remote:{
                url:userNameCheckUrl,
                message:'用户名或邮箱不存在',
                delay: 1000,
                data:function(){
                    return {
                        username:$("input[name='username']").val(),
                    };
                },
                type:'post',
            }
        }
    };
    $('#passwordEdit').bootstrapValidator('addField','username',validate);
});
function sendEmailCode(obj){
    var name = $('input[name="username"]').val();
    $(obj).attr('disabled','disabled');
    $.ajax({
        url:emailCodeUrl,
        type:'post',
        dataType:'json',
        data:{
            name:name,
        },
        success:function(data){
            if(!data.status) {
                wq_alert(data.info);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).text('请求成功');
            setTimeout(function(){
                $(obj).text('重新获取').removeAttr('disabled');
            },3000)
        },
        error:function(){
            wq_alert('可能服务器忙，请重试！');
            $(obj).removeAttr('disabled');
        }
    });
}