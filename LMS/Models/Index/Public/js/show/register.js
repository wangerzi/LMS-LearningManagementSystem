/**
 * Created by Administrator on 2017/1/23.
 */
$(function(){
    replaceWord(("input[name='username']"),"用户名");
    replaceWord(("input[name='email']"),"请输入邮箱");
    replaceWord(("input[name='password']"),"请输入密码");
    replaceWord(("input[name='password_2']"),"请确认密码");
    replaceWord(("input[name='verify']"),"验证码");

    $('.input-group.date.birth').datepicker({
        format      :   'yyyy-mm-dd',
        autoclose   :   true,
        language    :   'zh-CN',
        //title       :   '请选择生日',
        todayHighlight: true,           //今天高亮。
        todayBtn: true,
        orientation: "bottom left",
    });

    $("#code").click(function(){
        $(this).attr("src",function(e,oldVal){return oldVal+"&r="+Math.random()});
    });

    /*bootstrapValidator的验证*/

    /*JS表单验证*/
    $('#register').bootstrapValidator({
        verbose:false,//顺序验证。
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
                    stringLength:{
                        min:USER_MIN_NAME,
                        max:USER_MAX_NAME,
                        message:'用户名不得小于'+USER_MIN_NAME+'字并不得大于'+USER_MAX_NAME+'字',
                    },
                    regexp:{
                        regexp: /^[\u4E00-\u9FFFa-zA-z1-9_]*$/,
                        message: '用户名只能是中文、英文、数字、下划线.'
                    },
                    remote:{
                        url:nameCheckUrl,
                        message:'用户名已被注册',
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
            email:{
                validators:{
                    notEmpty:{
                        message:'邮箱不能为空'
                    },
                    emailAddress:{
                        message:'邮箱不符合规则'
                    },
                    remote:{
                        url:mailCheckUrl,
                        message:'邮箱已被注册',
                        delay:2000,
                        type:'post',
                        data:function(){
                            return {
                                email:$("input[name='email']").val(),
                                type:'password',
                            };
                        }
                    }

                }
            },
            sex:{
                validators:{
                    notEmpty:{
                        message:'性别为必选项',
                    }
                }
            },
            birth:{
                validators:{
                    notEmpty:{
                        message:'生日不能为空！'
                    },
                    date:{
                        format:'YYYY-MM-DD',
                        message:'生日格式不匹配'
                    },
                    callback:{
                        message:'生日不能在今天以后！',
                        callback:function(value,validator){
                            var m = new moment(value, 'YYYY-MM-DD', true);
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isBefore(today);//'2000-01-01' 形式的！
                        }
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
            },
            verify:{
                validators:{
                    notEmpty:{
                        message:'验证码不能为空',
                    },
                    stringLength:{
                        min:verifyLen,
                        max:verifyLen,
                        message:'验证码长度应为'+verifyLen,
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
        var form=$('#register');

        $('#submit').attr("disabled",'disabled');

        //为加密做准备
        var pas_1 = $('input[name="password"]');
        var pas_2 = $('input[name="password_2"]');

        var pwd_1 = pas_1;
        var pwd_2 = pas_2;

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
                    pas_1(pwd_1);
                    pas_2(pwd_2);
                    wq_alert(data.info);
                    $('#submit').attr("disabled",'disabled');//禁用按钮，除非有改动。
                    return 0;
                }
                wq_alert('注册成功，系统已发送注册邮件至您所指定的邮箱，激活后方可登录。',function(){
                    location.href=successUrl;
                });
            },
            error:function(xml,text){
                //恢复密码
                pas_1(pwd_1);
                pas_2(pwd_2);
                wq_alert(text+'可能服务器忙，请稍后重试！');
                $('#submit').removeAttr('disabled');
                return 0;
            }
        });
    });
});