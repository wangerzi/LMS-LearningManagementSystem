/**
 * Created by Administrator on 2016/11/23 0023.
 */
$(function(){
    $('#addStage').click(add_stage);
    //写文本框提示
    replaceWord($('#planName'),'请输入计划名称');

    //submit按钮一样得type='submit'，否则验证效果不佳，这样只提交一次
    $('#submit').click(function() {
        var form=$('#addPlan');
        var bootstrapValidator = $("#addPlan").data('bootstrapValidator');
        form.bootstrapValidator('validate');
        if(bootstrapValidator.isValid()){
            $('#submit').attr("disabled",'disabled');
        }
        return true;
    });

    /*JS表单验证*/
    $('#addPlan').bootstrapValidator({
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
                        message:'名称不能为空'
                    },
                    stringLength:{
                        min:PLAN_MIN_NAME,
                        max:PLAN_MAX_NAME,
                        message:'名称不得小于'+PLAN_MIN_NAME+'字并不得大于'+PLAN_MAX_NAME+'字',
                    }
                }
            },
            startTime:{
                validators:{
                    notEmpty:{
                        message:'开始时间不能为空！'
                    },
                    date:{
                        format:'YYYY-MM-DD',
                        message:'时间格式不匹配'
                    },
                    callback:{
                        message:'开始时间不能在今天以前！',
                        callback:function(value,validator){
                            var m = new moment(value, 'YYYY-MM-DD', true);
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isAfter(yesterday);//'2000-01-01' 形式的！
                        }
                    }
                }
            }
        }/*,submitHandler:function(validator,form,submitButton){
            //e.preventDefault();

            $(submitButton).attr("disabled",'disabled');

            //ajax提交
            $(form).ajaxSubmit({
                url:form.attr('action'),
                dataType:'json',
                type:'post',
                data:form.serialize(),
                success:function(data) {
                    if(!data.status){
                        wq_alert(data.info);
                        $(submitButton).attr("disabled",'disabled');
                        return 0;
                    }
                    location.href=successUrl;
                },
                error:function(xml,text){
                    wq_alert(text+'可能服务器忙，请稍后重试！');
                    $(submitButton).removeAttr('disabled');
                    return 0;
                }
            });
            return true;
         }*/
    }).on('success.form.bv',function(e){
        e.preventDefault();

        //获取form表单
        var form=$('#addPlan');

        $('#submit').attr("disabled",'disabled');

        //合理性验证
        var flag=false;

        var stages=$('.stage');

        if(stages.length<1){
            //wq_alert('一个计划至少有一个阶段，请点击[添加阶段]进行添加！');
            $('#addStage').click();
            return false;
        }

        $(stages).each(function(index){
            if($(this).find('.mission').length<1){
                flag=index;
            }
        });
        if(typeof flag=='number'){
            scrollTo(stages.eq(flag));
            stages.eq(flag).find('.add-mission-btn').click();
            //wq_alert('每个阶段至少一个任务');
            $('#submit').removeAttr('disabled');
            return false;
        }


        //ajax提交
        $(form).ajaxSubmit({
            url:form.attr('action'),
            dataType:'json',
            type:'post',
            success:function(data) {
                if(!data.status){
                    wq_alert(data.info);
                    $('#submit').attr("disabled",'disabled');
                    if(typeof data.index !='undefined'){
                        scrollTo(stages.eq(data.index-1));
                        stages.eq(data.index-1).find('.add-mission-btn').click();
                        return 0;
                    }
                    if(typeof data.stage !='undefined'){
                        $('#addStage').click()
                        return 0;
                    }
                    return 0;
                }
                location.href=successUrl;
            },
            error:function(xml,text){
                wq_alert(text+'可能服务器忙，请稍后重试！');
                $('#submit').removeAttr('disabled');
                return 0;
            }
        });
    });
});
function add_stage()
{
    n++;
    //阶段的代码！
    var stage='<div class="stage">'+
        '<label class="control-label col-md-12">第<span class="num">'+n+'</span>阶段</label>'+
        '<div class="col-md-6">'+
        '<div class="form-group">'+
            '<input type="text" name="stage&'+n+'" class="form-control" placeholder="阶段名称" style="margin-bottom:5px;" />'+
        '</div>'+
        '<div class="form-group">'+
            '<textarea name="stage_info&'+n+'" cols="30" rows="3" placeholder="阶段描述（选填）" class="form-control"></textarea>'+
        '</div>'+
        '</div>'+
        '<div class="col-md-6">'+
        '<button type="button" class="btn btn-danger" onclick="del_stage(this)">'+
        '<span class="glyphicon glyphicon-remove"></span>'+
        '删除阶段'+
        '</button> '+
        '<button type="button" class="btn btn-success add-mission-btn" onclick="add_mission(this,false)">'+
        '<span class="glyphicon glyphicon-plus"></span>'+
        '添加任务'+
        '</button>'+
        '</div>';
    $('#addStageArea').append(stage);

    var validate={
        validators:{
            notEmpty:{
                message:'阶段名不能为空！'
            },
            stringLength:{
                message:'阶段名需要在'+STAGE_MIN_NAME+'字 到 '+STAGE_MAX_NAME+'字之间',
                min:STAGE_MIN_NAME,
                max:STAGE_MAX_NAME,
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','stage&'+n,validate);
    validate={
        validators:{
            stringLength:{
                message:'阶段描述不能多于'+STAGE_MAX_NAME+'字！',
                max:STAGE_MAX_NAME,
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','stage_info&'+n,validate);
    //定位
    $('input[name="stage&'+n+'"]').focus();
}
/**
 * 添加任务
 * @param obj
 * @param isChild   是否添加同级任务？
 */
function add_mission(obj,isChild){
    //顶层任务
    var top;
    if(isChild)
        top=$(obj).parent().parent().parent();
    else
        top=$(obj).parent();
    //获取任务的数
    var num=Number(top.parent().find('.num').text());

    var mission='<div class="mission">'+
        '<label class="control-label col-md-11 col-md-offset-1">任务</label>'+
        '<div class="col-md-5 col-md-offset-1">'+
            '<div class="form-group">'+
                '<input type="text" name="mission&'+num+'[]" class="form-control" placeholder="任务名称" />'+
            '</div>'+
            '<div class="form-group">'+
                '<textarea name="mission_info&'+num+'[]" cols="30" rows="3" placeholder="任务描述"（选填） class="form-control"></textarea>'+
            '</div>'+
        '</div>'+
        '<div class="col-md-6">'+
            '<div class="col-md-12 form-group">'+
                '<button type="button" class="btn btn-warning" onclick="del_mission(this)">'+
                    '<span class="glyphicon glyphicon-remove-circle"></span>'+
                    '删除任务'+
                '</button> '+
                '<button type="button" class="btn btn-success" onclick="add_mission(this,true)">'+
                    '<span class="glyphicon glyphicon-plus"></span>'+
                    '添加同级任务'+
                '</button>'+
            '</div>'+
            '<div class="col-md-7">'+
                '<div class="form-group">'+
                    '<input type="text" name="mission_time&'+num+'[]" class="form-control" placeholder="学习时间(小时),默认6" />'+
                '</div>'+
            '</div>'+
        '</div>'+
        '</div>';
    top.after(mission);//用after有一个好处就是位置可以自由添加。

    var validate={
        validators:{
            notEmpty:{
                message:'任务名不能为空！'
            },
            stringLength:{
                message:'任务名需要在'+MISSION_MIN_NAME+'字 到 '+MISSION_MAX_NAME+'字之间',
                min:MISSION_MIN_NAME,
                max:MISSION_MAX_NAME
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','mission&'+n+'[]',validate);
    validate={
        validators:{
            stringLength:{
                message:'任务描述不能多于'+MISSION_MAX_INFO+'字！',
                max:MISSION_MAX_INFO,
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','mission_info&'+n+'[]',validate);
    validate={
        validators:{
            numeric:{
                message:'学习时间需要是数字'
            },
            callback:{
                message:'学习时间不得长于48h',
                callback:function(value,validator){
                    if(value>48)
                        return false;
                    return true;
                }
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','mission_time&'+n+'[]',validate);
    //定位
    top.next().find('input[type="text"]:first').focus();
}
function del_mission(obj){
    //if(confirm('真的要删除这个任务吗？'))
        $(obj).parent().parent().parent().remove();
   // $('#delModal').find('.name').text('任务').modal();
}
function del_stage(obj){
    wq_confirm('真的要删除这个阶段吗？',function(){
        $(obj).parent().parent().remove();
    });
}
/**
 * 为单选框加上提示信息
 * @param obj
 */
function showTips(obj){
    $(obj).parent().find('.help-block:first').text($(obj).attr('alt'));
}