/**
 * Created by Administrator on 2016/12/5 0005.
 */
$(function(){
    //replaceWord($("input[name='remind_everyday_time']"),'请填入提醒时间');
    //replaceWord($("input[name='remind_warning_time']"),'请填入提醒时间');
    $('#personInfo').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons:{
            /*验证状态用的图标！*/
            valid:'glyphicon glyphicon-ok',
            invalid:'glyphicon glyphicon-remove',
            validating:'glyphicon glyphicon-refresh'
        },
        fields:{
            remind_everyday_time:{
                validators:{
                    notEmpty:{
                        message:'每日提醒时间不能为空！'
                    },
                    time:{
                        format:'H:i',
                        message:'时间格式不匹配'
                    },
                    /*callback:{
                        message:'每日提醒不能在10:00以后！',
                        callback:function(value,validator){
                            alert(value);
                            var m = new moment(value, 'HH:mm');
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isBefore('10:00','minute');//'2000-01-01' 形式的！
                        }
                    }*/
                }
            },
            remind_warning_time:{
                validators:{
                    notEmpty:{
                        message:'每日警告时间不能为空！'
                    },
                    time:{
                        format:'H:i',
                        message:'时间格式不匹配'
                    },
                    /*callback:{
                        message:'每日警告不能在16:00以前！',
                        callback:function(value,validator){
                            alert(value);
                            var m = new moment(value, 'HH:mm', true);
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isAfter('16:00');//'2000-01-01' 形式的！
                        }
                    }*/
                }
            }
        }
    });
});