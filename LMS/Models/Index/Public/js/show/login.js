/**
 * Created by Administrator on 2017/1/23.
 */
$(function(){
    replaceWord(("input[name='username']"),"用户名/邮箱");
    replaceWord(("input[name='password']"),"请输入密码");
    replaceWord(("input[name='verify']"),"验证码");

    $("#code").click(function(){
        $(this).attr("src",function(e,oldVal){return oldVal+"&r="+Math.random()});
    });
    /*bootstrapValidator的验证*/

    /*JS表单验证*/
    $('#login').bootstrapValidator({
        verbose: false,
        message: 'This value is not valid',
        feedbackIcons:{
            /*验证状态用的图标！*/
            valid:'glyphicon glyphicon-ok',
            invalid:'glyphicon glyphicon-remove',
            validating:'glyphicon glyphicon-refresh'
        },
        fields:{
            username:{
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
            },
            password:{
                validators:{
                    notEmpty:{
                        message:'密码不能为空！'
                    },
                    remote:{
                        url:pwdCheckUrl,
                        message:'密码不正确',
                        delay: 1000,
                        data:function(){
                            var pas = hex_sha1($("input[name='password']").val());
                            return {
                                username:$("input[name='username']").val(),
                                xxx:pas,                //也不知道是什么奇葩，如果名字用password，死活传不了加密信息。
                            };
                        },
                        type:'post',
                    }
                }
            },
            verify:{
                validators:{
                    notEmpty:{
                        message:'验证码不能为空',
                    },
                    stringLength:{
                        min:verifyLen,
                        max:verifyLen,
                        message:'验证码长度应该为'+verifyLen,
                    },
                    remote:{
                        url:verifyCheckUrl,
                        message:'验证码不正确',
                        delay:1000,
                        data:function(){
                            return {
                                verify:$("input[name='verify']").val(),
                            };
                        },
                        type:'post',
                    }
                }
            }

        }
    }).on('success.form.bv',function(e){
        e.preventDefault();

        //获取form表单
        var form=$('#login');

        $('#submit').attr("disabled",'disabled');

        //加密
        var pas = $('input[name="password"]');
        var pwd_before = pas.val();
        pas.val(hex_sha1(pwd_before));
        //ajax提交
        $(form).ajaxSubmit({
            url:form.attr('action'),
            dataType:'json',
            type:'post',
            success:function(data) {
                if(!data.status){
                    pas.val(pwd_before);//恢复以前的密码。
                    wq_alert(data.info);
                    $('#submit').attr("disabled",'disabled');//禁用按钮，除非有改动。
                    return 0;
                }
                if(data.location!=null)
                    location.href=data.location;
                else
                    location.href=successUrl;
            },
            error:function(xml,text){
                pas.val(pwd_before);//恢复以前的密码。
                wq_alert(text+'可能服务器忙，请稍后重试！');
                $('#submit').removeAttr('disabled');
                return 0;
            }
        });
    });
});