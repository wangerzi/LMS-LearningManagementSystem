/**
 * Created by Administrator on 2016/12/5 0005.
 */
$(function(){
    replaceWord($("input[name='code']"),'请输入邮箱中的密匙');
    replaceWord($("input[name='password']"),'请输入密码');
    replaceWord($("input[name='password_2']"),'请确认密码');

    $('#passwordEdit').bootstrapValidator({
        verbose:false,
        message: 'This value is not valid',
        feedbackIcons:{
            /*验证状态用的图标！*/
            valid:'glyphicon glyphicon-ok',
            invalid:'glyphicon glyphicon-remove',
            validating:'glyphicon glyphicon-refresh'
        },
        fields:{
            code:{
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
                                type:'password',
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
                    stringLength:{
                        min:MIN_PAS,
                        max:MAX_PAS,
                        message:'密码长度需要在'+MIN_PAS+'至'+MAX_PAS+'之间',
                    }
                }
            },
            password_2:{
                validators:{
                    notEmpty:{
                        message:'密码不能为空！'
                    },
                    identical:{
                        field:'password',
                        message:'两次输入密码不相同',
                    }
                }
            }
        }
    }).on('success.bv.form',function(e){
        e.preventDefault();

        //获取form表单
        var form=$('#passwordEdit');

        $('#submit').attr("disabled",'disabled');

        //为加密做准备
        var pas_1 = $('input[name="password"]');
        var pas_2 = $('input[name="password_2"]');

        var pwd_1 = pas_1.val();
        var pwd_2 = pas_2.val();

        pas_1.val(hex_sha1(pwd_1));
        pas_2.val(hex_sha1(pwd_2));

        //ajax提交
        $(form).ajaxSubmit({
            url:form.attr('action'),
            dataType:'json',
            type:'post',
            success:function(data) {
                if(!data.status){
                    //恢复密码
                    pas_1.val(pwd_1);
                    pas_2.val(pwd_2);
                    wq_alert(data.info);
                    $('#submit').removeAttr('disabled');
                    //$('#submit').attr("disabled",'disabled');//禁用按钮，除非有改动。
                    return 0;
                }
                wq_alert('密码修改成功，请重新登录',function(){
                    location.href=successUrl;
                });
            },
            error:function(xml,text){
                //恢复密码
                pas_1.val(pwd_1);
                pas_2.val(pwd_2);
                wq_alert(text+'可能服务器忙，请稍后重试！');
                $('#submit').removeAttr('disabled');
                return 0;
            }
        });
    });
});