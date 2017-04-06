/**
 * Created by Administrator on 2016/12/8 0008.
 */
$(function () {
    replaceWord($("input[name='code']"),'请输入邮箱验证码');
    $('#emailCheck').bootstrapValidator({
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
            }
        }
    })
});
function sendEmailCode(obj){
    $(obj).attr('disabled','disabled');
    $.ajax({
        url:emailCodeUrl,
        type:'post',
        dataType:'json',
        success:function(data){
            if(!data.status) {
                wq_alert(data.text);
                return 0;
            }
            $(obj).text('请求成功');
        },
        error:function(){
            wq_alert('可能服务器忙，请重试！');
            $(obj).removeAttr('disabled');
        }
    });
}