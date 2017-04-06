/**
 * Created by Administrator on 2016/12/5 0005.
 */
$(function(){
    replaceWord($("input[name='username']"),'请输入新用户名');
    replaceWord($("input[name='info']"),'他（她）没有留下自我介绍哦！');
    $('#basicInfo').bootstrapValidator({
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
                    verbose:false,
                    notEmpty:{
                        message:'用户名不能为空'
                    },
                    stringLength:{
                        min:MIN_NAME,
                        max:MAX_NAME,
                        message:'用户名长度需要是'+MIN_NAME+'字到'+MAX_NAME+'字之间！',
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
            info:{
                validators:{
                    verbose:false,
                    stringLength:{
                        min:0,
                        max:200,
                        message:'简介长度需要是'+0+'字到'+200+'字之间！',
                    },
                }
            },
            sex:{
                validators:{
                    notEmpty:{
                        message:'性别是必选项',
                    }
                }
            },
            birth:{
                validators:{
                    notEmpty:{
                        message:'开始时间不能为空！'
                    },
                    date:{
                        format:'YYYY-MM-DD',
                        message:'时间格式不匹配'
                    },
                    callback:{
                        message:'生日不能在今天以后！',
                        callback:function(value,validator){
                            var m = new moment(value, 'YYYY-MM-DD', true);
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isBefore(yesterday);//'2000-01-01' 形式的！
                        }
                    }
                }
            }
        }
    });
});