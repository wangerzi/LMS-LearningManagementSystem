/**
 * Created by Administrator on 2016/12/2 0002.
 */
$(function () {
    replaceWord($("input[name='title']"),'请输入标题');
    replaceWord($("textarea[name='content']"),'请在此输入内容');
    $('#sendMail').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons:{
            /*验证状态用的图标！*/
            valid:'glyphicon glyphicon-ok',
            invalid:'glyphicon glyphicon-remove',
            validating:'glyphicon glyphicon-refresh'
        },
        fields:{
            title:{
                validators:{
                    notEmpty:{
                        message:'私信标题不能为空'
                    },
                    stringLength:{
                        min:MESS_MIN_NAME,
                        max:MESS_MAX_NAME,
                        message:'私信标题不得小于'+MESS_MIN_NAME+'字并不得大于'+MESS_MAX_NAME+'字',
                    }
                }
            },
            content:{
                validators:{
                    notEmpty:{
                        message:'私信标题不能为空'
                    },
                    stringLength:{
                        min:MESS_MIN_CONTENT,
                        max:MESS_MAX_CONTENT,
                        message:'私信内容不得小于'+MESS_MIN_CONTENT+'字并不得大于'+MESS_MAX_CONTENT+'字',
                    }
                }
            },
            uid:{
                validators:{
                    notEmpty:{
                        message:'需要选择收信人'
                    }
                }
            }
        },
        submitHandler:function(validator,form,submitButton){
            $('#addPlan').bootstrapValidator('disableSubmitButtons',false).defaultSubmit();
        }
    })
});