/**
 * Created by Administrator on 2016/12/5 0005.
 */
$(function(){
    replaceWord($("input[name='code']"),'请输入邮箱中的密匙');
    replaceWord($("input[name='password']"),'请输入密码');
    replaceWord($("input[name='password_2']"),'请确认密码');

    $('#passwordEdit').bootstrapValidator({
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
                    identical:{
                        field:'password_2',
                        message:'两次输入密码不相同',
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
        },
        submitHandler:function(validator,form,submitButton){
            $('#addPlan').bootstrapValidator('disableSubmitButtons',false).defaultSubmit();
        }
    })
});