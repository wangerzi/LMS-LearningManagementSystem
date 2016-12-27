/**
 * Created by Administrator on 2016/12/5 0005.
 */
$(function(){
    var mail=$("input[name='mail']");
    var button=$("#sendNewEmail");
    replaceWord($("input[name='code']"),'请输入邮箱中的密匙');
    replaceWord(mail,'请输入新邮箱');

    $('#mailSetNew').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons:{
            /*验证状态用的图标！*/
            valid:'glyphicon glyphicon-ok',
            invalid:'glyphicon glyphicon-remove',
            validating:'glyphicon glyphicon-refresh'
        },
        fields:{
            mail:{
                validators:{
                    notEmpty:{
                        message:'新邮箱不能为空'
                    },
                    emailAddress:{
                        message:'邮箱不符合规则'
                    },
                    callback:{
                        message:'不能和现有邮箱相同',
                        callback:function(){
                            var mail=$("input[name='mail']").val();
                            var res=mail!=nowEmail;
                            if(res)
                                $(button).removeAttr('disabled');

                            return res;
                        }
                    },
                    remote:{
                        url:mailCheckUrl,
                        message:'邮箱已被注册',
                        delay:2000,
                        type:'post',
                        data:function(){
                            return {
                                email:$("input[name='mail']").val(),
                                type:'password',
                            };
                        }
                    }
                }
            },
            code:{
                validators:{
                    verbose:false,
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
                                mail:$("input[name='mail']").val(),
                            };
                        },
                        type:'post',
                    }
                }
            }
        }
    })
});
function sendNewEmailCode(obj){
    $(obj).attr('disabled','disabled');
    $.ajax({
        url:emailCodeUrl,
        type:'post',
        dataType:'json',
        data:{
            mail:$("input[name='mail']").val(),
        },
        success:function(data){
            if(!data.status) {
                wq_alert(data.text);
                $(obj).text('重新请求');
                $(obj).removeAttr('disabled');
                return 0;
            }
            wq_alert('请求成功，请前往邮箱确认！');
            setTimeout(function(){
                $(obj).text('重新请求');
                $(obj).removeAttr('disabled');
            },2000);
        },
        error:function(){
            wq_alert('可能服务器忙，请重试！');
            $(obj).removeAttr('disabled');
        }
    });
}