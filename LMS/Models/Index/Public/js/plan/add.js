/**
 * Created by Administrator on 2016/11/23 0023.
 */
$(function(){
    $('#addStage').click(add_stage);
    //写文本框提示
    replaceWord($('#planName'),'请输入计划名称');

    //调用datepicker插件
    $('.input-daterange').datepicker({
        format: "yyyy-mm-dd",
        language:'zh-CN',
        //autoclose: true,
        startDate: today,
        todayHighlight: true
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
            start:{
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
            },
            end:{
                validators:{
                    notEmpty:{
                        message:'结束时间不能为空！'
                    },
                    date:{
                        format:'YYYY-MM-DD',
                        message:'时间格式不匹配'
                    },
                    callback:{
                        message:'结束时间不能在开始时间以前！',
                        callback:function(value,validator){
                            var m = new moment(value, 'YYYY-MM-DD', true);
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isAfter($('input[name="start"]').val());//'2000-01-01' 形式的！
                        }
                    }
                }
            }
        }
    }).on('success.form.bv',function(e){
        e.preventDefault();

        //获取form表单
        var form=$('#addPlan');

        //合理性验证
        var flag=false;

        var stages=$('.stage');

        if(stages.length<1){
            //wq_alert('一个计划至少有一个阶段，请点击[添加步骤]进行添加！');
            $('#addStage').click();
            $('#submit').removeAttr('disabled');
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
            //wq_alert('每个步骤至少一个任务');
            $('#submit').removeAttr('disabled');
            return false;
        }

        $('#submit').attr("disabled",'disabled');


        //ajax提交
        $(form).ajaxSubmit({
            url:form.attr('action'),
            dataType:'json',
            type:'post',
            success:function(data) {
                if(!data.status){
                    wq_alert(data.info);
                    $('#submit').attr("disabled",'disabled');
                    //步骤中至少应该有一个任务。
                    if(typeof data.index !='undefined'){
                        wq_alert('每个步骤至少应有一个任务',function(){scrollTo(stages.eq(data.index-1));stages.eq(data.index-1).find('.add-mission-btn').click();});
                        return 0;
                    }
                    //至少有一个步骤。
                    if(typeof data.stage !='undefined'){
                        wq_alert('每个计划至少有一个步骤',function(){$('#addStage').click();});
                        return 0;
                    }
                    return 0;
                }
                wq_alert('添加成功，点击跳转至计划管理页面',function(){location.href=successUrl;});
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
    //步骤的代码！
    var stage='<div class="stage">'+
        '<label class="control-label col-md-12">步骤 <span class="num" style="display:none;">'+n+'</span></label>'+
        '<div class="col-md-6">'+
            '<div class="form-group">'+
                '<input type="text" name="stage&'+n+'" class="form-control" placeholder="步骤名称" style="margin-bottom:5px;" />'+
            '</div>'+
            '<div class="form-group">'+
                '<textarea name="stage_info&'+n+'" cols="30" rows="3" placeholder="步骤描述（选填）" class="form-control"></textarea>'+
            '</div>'+
        '</div>'+
        '<div class="col-md-6">' +
        '<div class="col-md-12">' +
            '<div class="form-group">' +
                '<button type="button" class="btn btn-danger" onclick="del_stage(this)">'+
                '<span class="glyphicon glyphicon-remove"></span>'+
                '删除步骤'+
                '</button> '+
                '<button type="button" class="btn btn-success add-mission-btn" onclick="add_mission(this,false)">'+
                '<span class="glyphicon glyphicon-plus"></span>'+
                '添加任务'+
                '</button>' +
            '</div>' +
        '</div>'+
        '<div class="col-md-7">'+
            '<div class="form-group">'+
            '<input type="text" name="stage_power&'+n+'" class="form-control" placeholder="权值默认10" />'+
            '<p class="help-block">注：权值越高，分配在此步骤的时间就越多</p>'+
            '</div>'+
        '</div>' +
        '</div>';
    $('#addStageArea').append(stage);

    var validate={
        validators:{
            notEmpty:{
                message:'步骤名不能为空！'
            },
            stringLength:{
                message:'步骤名需要在'+STAGE_MIN_NAME+'字 到 '+STAGE_MAX_NAME+'字之间',
                min:STAGE_MIN_NAME,
                max:STAGE_MAX_NAME,
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','stage&'+n,validate);
    validate={
        validators:{
            stringLength:{
                message:'步骤描述不能多于'+STAGE_MAX_NAME+'字！',
                max:STAGE_MAX_NAME,
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','stage_info&'+n,validate);
    validate={
        validators:{
            numeric:{
                message:'权值需要是数字'
            },
            callback:{
                message:'权值不得长于1000',
                callback:function(value,validator){
                    if(value>999)
                        return false;
                    return true;
                }
            }
        }
    };
    $('#addPlan').bootstrapValidator('addField','stage_power&'+n,validate);
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
        top=$(obj).parents('.mission:first');
    else
        top=$(obj).parents('.stage:first');
    //获取任务的数
    var num=Number(top.find('.num:first').text());

    var mission='<div class="mission">'+
        '<label class="control-label col-md-11 col-md-offset-1">任务</label>' +
        '<span class="num" style="display: none;">'+num+'</span>'+
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
        '</div>'+
        '</div>';
    if(isChild)
        top.after(mission);//添加同级任务。
    else
        top.append(mission);//用after有一个好处就是位置可以自由添加。

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
    //定位
    if(isChild)
        top.next().find('input[type="text"]:first').focus();
    else
        top.find('.mission:last input[type="text"]:first').focus();
}
function del_mission(obj){
    //if(confirm('真的要删除这个任务吗？'))
        $(obj).parents('.mission:first').remove();
   // $('#delModal').find('.name').text('任务').modal();
}
function del_stage(obj){
    wq_confirm('真的要删除这个步骤吗？',function(){
        $(obj).parents('.stage:first').remove();
    });
}
/**
 * 为单选框加上提示信息
 * @param obj
 */
function showTips(obj){
    $(obj).parent().find('.help-block:first').text($(obj).attr('alt'));
}