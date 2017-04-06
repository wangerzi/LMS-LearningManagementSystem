/**
 * Created by Administrator on 2017/1/30.
 */
$(function () {
    replaceWord($('input[name="name"]'),'怎么称呼您？');
    replaceWord($('textarea[name="content"]'),'请输入留言内容...');
    replaceWord($('input[name="connect"]'),'QQ/电话号码');
    replaceWord($('input[name="verify"]'),'请输入验证码');

    $("#code").click(function(){
        $(this).attr("src",function(e,oldVal){return oldVal+"&r="+Math.random()});
    });

    /*JS表单验证*/
    $('#feedback').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons:{
            /*验证状态用的图标！*/
            valid:'glyphicon glyphicon-ok',
            invalid:'glyphicon glyphicon-remove',
            validating:'glyphicon glyphicon-refresh'
        },
        fields:{
            name:{
                validators:{
                    notEmpty:{
                        message:'留名不能为空',
                    },
                    stringLength:{
                        min:nameMinLen,
                        max:nameMaxLen,
                        message:'留名长度不能大于'+nameMaxLen,
                    }
                }
            },
            content:{
                validators:{
                    notEmpty:{
                        message:'反馈内容不能为空'
                    },
                    stringLength:{
                        min:1,
                        max:contentMaxLen,
                        message:'反馈内容不能多于'+contentMaxLen,
                    }
                }
            },
            connect:{
                validators:{
                    regexp: {
                        regexp: /^1[3|5|8]{1}[0-9]{9}|[0-9]{3,12}$/,
                        message: '请输入正确的 QQ/电话号码'
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
        var form=$('#feedback');

        $('#submit').attr("disabled",'disabled');

        //ajax提交
        $(form).ajaxSubmit({
            url:form.attr('action'),
            dataType:'json',
            type:'post',
            success:function(data) {
                if(!data.status){
                    wq_alert(data.info);
                    $('#submit').attr("disabled",'disabled');//禁用按钮，除非有改动。
                    return 0;
                }
                wq_alert('反馈成功，谢谢您的反馈！',function(){
                    location.reload();
                    //$(form).find('[type="reset"]').click();
                    //history.back();
                });
            },
            error:function(xml,text){
                wq_alert(text+'可能服务器忙，请稍后重试！');
                $('#submit').removeAttr('disabled');
                return 0;
            }
        });
    });
});