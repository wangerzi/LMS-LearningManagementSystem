/**
 * Created by Administrator on 2016/11/23 0023.
 */
$(function(){
    $('.mission').on('mouseover',function(e){
        $(this).find('.operate').show();
    }).on('mouseout',function(e){
        //没有保存按钮则隐藏
        if($(this).find('.save').length<1)
            $(this).find('.operate').hide()
    });

    $('#basicForm .save').click(function(){
        submitForm($(this).parents('form'));
    });

    $('#faceForm .save').click(function(){
        submitForm($(this).parents('form'),function (data) {
            wq_alert('保存成功');
            $('#faceImg').attr('src',data.face);
        });
    });
    $('#supervisionFrom .save').click(function(){
        submitForm($(this).parents('form'),function(data){
            wq_alert('已成功向'+data.num+'个好友发送您的申请（跳过已经送过的好友）');
        });
    });

    //ajax提交

    /*$('.stage').hover(function(e){
        $(this).find('.operate .save').show();
    }).mouseleave(function(e){
        $(this).find('.operate .save').hide()
    });*/
    //这里是初始化的，直接就可以更改的
    $('.mission,.stage').each(function(){
        bind_check_change(this,function(obj){
            var id=$(obj).attr('data');

            if(!$(obj).find('.operate .save').length) {
                //分类callback.
                if($(obj).is('.stage')){
                    $(obj).find('.operate').prepend(
                        '<button class="btn btn-success save" type="button" data="' + id + '" onclick="save_stage(this)">' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                        '保存修改' +
                        '</button>'
                    );
                }else{
                    $(obj).find('.operate').prepend(
                        '<button class="btn btn-success save" type="button" data="' + id + '" onclick="save_mission(this)">' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                        '保存修改' +
                        '</button>'
                    );
                }
            }
        });
    });
});
function submitForm(obj,callback){
    var form=$(obj);
    var submit=form.find('.save');
    $(submit).attr('disabled','disabled');

    var oldValue=$(submit).html();
    $(submit).html(
        '<span class="glyphicon glyphicon-refresh"></span>' +
        '保存中'
    );

    form.ajaxSubmit({
        url:form.attr('action'),
        dataType:'json',
        data:{
            pid :   pid,
            pcid:   pcid,
        },
        type:'post',
        success:function(data) {
            if(!data.status){
                wq_alert(data.info);
                submit.removeAttr('disabled');
                submit.html(oldValue);
                return 0;
            }
            if(typeof callback  ==   'function')
                callback(data);
            else{
                wq_alert('保存成功');
            }

            if(typeof data.uniqid != 'undifined')
                $(form).find('input[name="uniqid"]').val(data.uniqid);
            submit.removeAttr('disabled');
            submit.html(oldValue);
        },
        error:function(xml,text){
            wq_alert(text+'可能服务器忙，请稍后重试！');
            submit.removeAttr('disabled');
            submit.html(oldValue);
            return 0;
        }
    });
}
/**
 * 检查一组数据是否改变
 * @param isNew 是否是新建任务
 * @param isStage   是否是新建任务
 * @param obj
 */
function bind_check_change(obj,change,notChange){
    $(obj).find('input')
        .unbind('keyup')
        .bind('keyup',function(){
            var flag=false;
            $(obj).find('input').each(function () {
                if($(this).val()!=$(this).attr('value'))
                    flag=true;
            });
            //改变了，增加处理操作
            if(flag) {
                if(typeof change == 'function')
                    change(obj);
            }
            else {
                if(typeof notChange == 'function')
                    notChange(obj);
                else{//默认的处理
                    $(obj).find('.save').remove();
                }
            }
        });
}
/**
 * 添加阶段
 * @param obj   添加的按钮对象
 * @param isStageNew 是否是新建阶段的新建任务
 */
function add_mission(obj,isStageNew)
{
    var top=$(obj).parents('tr');//从这个tr到下一个tr

    var id=$(obj).attr('data');
    top.after(
        '<tr class="mission" data="'+id+'">' +
        '   <td></td>' +
        '   <td>' +
        '       <div class="form-group">' +
        '           <input class="form-control" name="mission&'+id+'" data="'+id+'" value="" type="text" />' +
        '           <span class="small text-nowrap text-danger help"></span>'+
        '       </div>'+
        '   </td>' +
        '   <td>' +
        '       <div class="form-group">' +
        '           <input class="form-control" name="mission_info&'+id+'" data="'+id+'" value="" type="text" />' +
        '           <span class="small text-nowrap text-danger help"></span>'+
        '       </div>'+
        '   </td>' +
        '   <td>' +
        '       <div class="form-group">' +
        '           <input class="form-control" name="mission_hour&'+id+'" data="'+id+'" value="6" type="text" />' +
        '           <span class="small text-nowrap text-danger help"></span>'+
        '       </div>'+
        '   </td>' +
        '   <td>' +
        '       <div class="form-group">' +
        '           <input class="form-control" name="mission_sort&'+id+'" data="'+id+'" value="9" type="text" />' +
        '           <span class="small text-nowrap text-danger help"></span>'+
        '       </div>'+
        '   </td>' +
        '   <td>' +//操作区
        '       <div class="btn-group-vertical btn-group-sm operate">' +
        '           <button class="btn btn-danger" type="button" onclick="remove_mission_new(this)">' +
        '               <span class="glyphicon glyphicon-remove"></span>' +
        '               取消新建' +
        '           </button>' +
        '       </div>' +
        '   </td>' +
        '</tr>'
    );
    var newMission=top.next();
    //焦点
    newMission.find('input[name="mission&'+id+'"]').focus();

    //bindMissionCheck(newMission);

    //新建，不是阶段 或 新建是阶段
    if(isStageNew){
        bind_check_change(top.next(),function(obj){
           /* var id=$(obj).attr('data');

            if(!$(obj).find('.operate .save').length) {
                $(obj).find('.operate').prepend(
                    '<button class="btn btn-success save" type="button" data="' + id + '" onclick="save_mission_new(this)">' +
                    '<span class="glyphicon glyphicon-check"></span>' +
                    '保存新建' +
                    '</button>'
                );
            }*/
        });
    }else{
        bind_check_change(top.next(),function(obj){
            var id=$(obj).attr('data');

            if(!$(obj).find('.operate .save').length) {
                $(obj).find('.operate').prepend(
                    '<button class="btn btn-success save" type="button" data="' + id + '" onclick="save_mission_new(this)">' +
                    '<span class="glyphicon glyphicon-check"></span>' +
                    '保存新建' +
                    '</button>'
                );
            }
        });
    }
}
/**
 * 传入一个总的obj，将里边的表单绑定上判断    --  已舍弃，因为太麻烦了，效果也没有bootstrap的好
 * @param obj
 */
function bindMissionCheck(obj){
    for(var i=0;i<obj.length;i++){
        var id=$(obj[i]).attr('data');
        var name=obj.find('input[name="mission&'+id+'"]');
        var info=obj.find('input[name="mission_info&'+id+'"]');
        var hour=obj.find('input[name="mission_hour&'+id+'"]');
        var sort=obj.find('input[name="mission_sort&'+id+'"]');

        name.keydown(function(){
            var str=$(this).val();
            var help=$(this).parents('.form-group').find('.help');
            if(str.length<MISSION_MIN_NAME-1||str.length>MISSION_MAX_NAME-1){
                help.text('任务名长度需要在'+MISSION_MIN_NAME+'到'+MISSION_MAX_NAME+'之间！');
            }else{
                help.text('');
            }
        });
        info.keydown(function(){
            var str=$(this).val();
            var help=$(this).parents('.form-group').find('.help');
            if(str.length>MISSION_MAX_INFO-1){
                $(this).parents('.form-group').find('.help').text('任务简介长度需要在'+0+'到'+MISSION_MAX_INFO+'之间！');
                $(this).focus();
            }else{
                help.text('');
            }
        });
        hour.keydown(function(){
            var str=$(this).val();
            var help=$(this).parents('.form-group').find('.help');
            if(str<2||str>48){
                $(this).parents('.form-group').find('.help').text('学习时长需要在'+2+'到'+48+'之间！');
                $(this).focus();
            }else{
                help.text('');
            }
        });
        sort.keydown(function(){
            var str=$(this).val();
            var help=$(this).parents('.form-group').find('.help');
            if(str<0){
                $(this).parents('.form-group').find('.help').text('优先级不得小于0');
                $(this).focus();
            }else{
                help.text('');
            }
        });
    }
}
/**
 * 检查mission是否正确
 * @param obj
 * @returns {boolean}
 */
function check_mission(obj){
    var totalFlag=true;

    for(var i=0;i<obj.length;i++){
        var id=$(obj[i]).attr('data');
        var name=obj.find('input[name="mission&'+id+'"]');
        var info=obj.find('input[name="mission_info&'+id+'"]');
        var hour=obj.find('input[name="mission_hour&'+id+'"]');
        var sort=obj.find('input[name="mission_sort&'+id+'"]');

        //分开是因为name对象可能后边还有用
        var name_str=name.val();
        var info_str=info.val();
        var hour_str=hour.val();
        var sort_str=sort.val();

        var flag=true;

        //名字的检测
        var help=name.parents('.form-group').find('.help');
        if(name_str.length<MISSION_MIN_NAME||name_str.length>MISSION_MAX_NAME){
            help.text('任务名长度需要在'+MISSION_MIN_NAME+'到'+MISSION_MAX_NAME+'之间！');
            name.focus();
            flag=false;
        }else{
            help.text('');
        }

        //简介的检测
        var help=info.parents('.form-group').find('.help');
        if(info_str.length>MISSION_MAX_INFO){
            help.text('任务简介长度需要在'+0+'到'+MISSION_MAX_INFO+'之间！');
            info.focus();
            flag=false;
        }else{
            help.text('');
        }

        //学习时长的检测
        var help=hour.parents('.form-group').find('.help');
        if(hour_str<2||hour_str>48){
            help.text('学习时长需要在'+2+'到'+48+'之间！');
            hour.focus();
            flag=false;
        }else{
            help.text('');
        }

        //优先级的检测
        var help=sort.parents('.form-group').find('.help');
        if(sort_str<0){
            help.text('优先级不得小于0');
            sort.focus();
            flag=false;
        }else{
            help.text('');
        }
        totalFlag=totalFlag&&flag;//只要有一个错误，整体返回错误
    }
    return totalFlag;
}
/**
 * 检查stage是否正确
 * @param obj
 * @returns {boolean}
 */
function check_stage(obj){
    var totalFlag=true;

    for(var i=0;i<obj.length;i++){
        var id=$(obj[i]).attr('data');
        var name=$(obj[i]).find('input[name="stage&'+id+'"]');
        var info=$(obj[i]).find('input[name="stage_info&'+id+'"]');
        var sort=$(obj[i]).find('input[name="stage_sort&'+id+'"]');

        //分开是因为name对象可能后边还有用
        var name_str=name.val();
        var info_str=info.val();
        var sort_str=sort.val();

        var flag=true;

        //名字的检测
        var help=name.parents('.form-group').find('.help');
        if(name_str.length<STAGE_MIN_NAME||name_str.length>STAGE_MAX_NAME){
            help.text('阶段名长度需要在'+STAGE_MIN_NAME+'到'+STAGE_MAX_NAME+'之间！');
            name.focus();
            flag=false;
            return false;
        }else{
            help.text('');
        }

        //简介的检测
        var help=info.parents('.form-group').find('.help');
        if(info_str.length>STAGE_MAX_INFO){
            help.text('阶段简介长度需要在'+0+'到'+STAGE_MAX_INFO+'之间！');
            info.focus();
            flag=false;
            return false;
        }else{
            help.text('');
        }

        //优先级的检测
        var help=sort.parents('.form-group').find('.help');
        if(sort_str<0){
            help.text('优先级不得小于0');
            sort.focus();
            flag=false;
            return false;
        }else{
            help.text('');
        }
        totalFlag=totalFlag&&flag;//只要有一个错误，整体返回错误
    }
    return totalFlag;
}
/**
 * 在末尾添加一个阶段，同时锁定不能继续添加阶段，除非添加阶段成功
 */
function add_stage(){
    var not_save=$('.stage-not-save');
    if(not_save.length){
        wq_alert('请先保存【未保存阶段】后，再【新建阶段】',function(){
            scrollTo(not_save);
            not_save.find('input[type="text"]:first').focus();
        });
        return ;
    }
    n++;
    //需要新建一个任务，确保每个stage都有一个阶段
    $('.mission:last').after(
        '<tr class="stage stage-not-save" data="'+n+'" style="background:#f9f9f9;">'+
        '   <td>'+ n +
        '   </td>' +
        '   <td>'+
        '    <div class="form-group">'+
        '<input class="form-control" name="stage&'+n+'" data="'+n+'" type="text" value="" />' +
        '<span class="small text-nowrap text-danger help"></span>'+
        '</div>'+
        '</td>'+
        '<td>'+
        '<div class="form-group">'+
        '<input class="form-control" name="stage_info&'+n+'" data="'+n+'" type="text" value="" />' +
        '<span class="small text-nowrap text-danger help"></span>'+
        '</div>'+
        '</td>'+
        '<td>'+
        '</td>'+
        '<td>'+
        '<div class="form-group">'+
        '<input class="form-control" name="stage_sort&'+n+'" data="'+n+'" type="text" value="9" />' +
        '<span class="small text-nowrap text-danger help"></span>'+
        '</div>'+
        '</td>'+
        '<td>'+
        '<div class="btn-group-vertical btn-group-sm operate">'+
        '<button class="btn btn-warning add" type="button" data="'+n+'" onclick="add_mission(this,true)">'+
        '<span class="glyphicon glyphicon-plus"></span>'+
        '新建任务'+
        '</button>'+
        '<button class="btn btn-danger" type="button" onclick="remove_stage_new(this)">'+
        '<span class="glyphicon glyphicon-trash"></span>'+
        '取消新建'+
        '</button>'+
        '</div>'+
        '</td>'
    );
    var newStage=$('.mission:last').next();//最后一个任务的next即为新建的stage

    //帮助用户新建一个任务
    //newStage.find('.operate .add').click();
    bind_check_change(newStage,function(obj){
        if(!$(obj).find('.operate .save').length) {
            $(obj).find('.operate').prepend(
                '<button class="btn btn-success save" type="button" onclick="save_stage_new(this)">' +
                '<span class="glyphicon glyphicon-check"></span>' +
                '保存新建' +
                '</button>'
            );
        }
    });
}
/**
 * 保存新建阶段
 * @param obj
 */
function save_stage_new(obj){
    var stage=$(obj).parents('.stage');
    var next=$(obj).parents('tr:first').nextAll();
    if(!next.length){
        wq_alert('每个阶段至少有一个任务');
        $(stage).find('.operate .add').click();
        return 0;
    }

    if(!check_stage(stage)){
        return 0;
    }

    //检查合理性
    for(var i=0;i<next.length;i++){
        if(!check_mission($(next[i]))){
            return 0;
        }
    }

    var missions=new Array();
    next.each(function(){
        //不是.mission则不作处理
        if(!$(this).is('.mission'))
            return 0;
        id=$(this).attr('data');
        var mission={
            name: $(this).find('input[name="mission&' + id + '"]').val(),
            info: $(this).find('input[name="mission_info&' + id + '"]').val(),
            hour: $(this).find('input[name="mission_hour&' + id + '"]').val(),
            sort: $(this).find('input[name="mission_sort&' + id + '"]').val(),
        };
        missions.push(mission);
    });

    if(missions.length<1){
        wq_alert('每个阶段至少有一个任务');
        return 0;
    }

    //进行ajax操作。

    var id=$(stage).attr('data');
    //这一块还得留着。。
    var name=$(stage).find('input[name="stage&'+id+'"]');
    var info=$(stage).find('input[name="stage_info&'+id+'"]');
    var sort=$(stage).find('input[name="stage_sort&'+id+'"]');

    //分开是因为name对象可能后边还有用
    var name_str=name.val();
    var info_str=info.val();
    var sort_str=sort.val();

    ajaxOperation(obj,addStageUrl,{
        pid:pid,
        name:name_str,
        info:info_str,
        sort:sort_str,
        mission:missions,
        uniqid:operate_uniqid,
    },function(data){
        if(!data.status){
            wq_alert(data.info);
            $(obj).removeAttr('disabled');
            return 0;
        }
        //动态更新验证码，虽然感觉这个没啥用的样子
        if(data.uniqid)
            operate_uniqid=data.uniqid;

        //获取到sid之后，修改stage的信息
        var sid=data.sid;

        //不知为啥要重新获取。。
        name=$(stage).find('input[name="stage&'+id+'"]');
        info=$(stage).find('input[name="stage_info&'+id+'"]');
        sort=$(stage).find('input[name="stage_sort&'+id+'"]');

        //更新每个的name
        name.attr('data',sid).attr('name','stage&'+sid).attr('value',$(name).val());
        info.attr('data',sid).attr('name','stage_info&'+sid).attr('value',$(info).val());
        sort.attr('data',sid).attr('name','stage_sort&'+sid).attr('value',$(sort).val());
        $(stage).attr('data',sid).removeClass('stage-not-save');
        //alert($(stage).length);

        $(stage).find('.operate').html(
            '<button class="btn btn-warning" type="button" data="'+sid+'" onclick="add_mission(this)">' +
            '<span class="glyphicon glyphicon-plus"></span>' +
            '新建任务' +
            '</button>'+
            '<button class="btn btn-danger" type="button" data="'+sid+'" onclick="remove_stage(this)">' +
            '<span class="glyphicon glyphicon-trash"></span>' +
            '移除阶段' +
            '</button>'
        );

        $(obj).remove();//更新状态
        bind_check_change(stage,function(obj){
            var id=$(obj).attr('data');

            if(!$(obj).find('.operate .save').length) {
                $(obj).find('.operate').prepend(
                    '<button class="btn btn-success save" type="button" data="' + id + '" onclick="save_stage(this)">' +
                    '<span class="glyphicon glyphicon-ok"></span>' +
                    '保存修改' +
                    '</button>'
                );
            }
        });

        //循环给nextAll里边的更新状态
        for(i=0;i<next.length;i++){
            //任务的id
            var mid=data.mid[i];
            var mission=next[i];

            //id还是以前的那个ID
            var name=$(mission).find('input[name="mission&'+id+'"]');
            var info=$(mission).find('input[name="mission_info&'+id+'"]');
            var hour=$(mission).find('input[name="mission_hour&'+id+'"]');
            var sort=$(mission).find('input[name="mission_sort&'+id+'"]');

            var name_str=name.val();
            var info_str=info.val();
            var hour_str=hour.val();
            var sort_str=sort.val();

            //更新任务id，以及更新vlaue，否则检测修改可能错误！
            name.attr('data',mid).attr('name','mission&'+mid).attr('value',name_str);
            info.attr('data',mid).attr('name','mission_info&'+mid).attr('value',info_str);
            hour.attr('data',mid).attr('name','mission_hour&'+mid).attr('value',hour_str);
            sort.attr('data',mid).attr('name','mission_sort&'+mid).attr('value',sort_str);
            $(mission).attr('data',mid);//next[i]相当于JS对象，需要重新实例化

            //更改按钮组的功能
            $(mission).find('div.operate:first').html(
                /* '<button class="btn btn-success save" type="button" data="{$val.id}" onclick="save_mission(this)" style="display: none;">' +
                 '<span class="glyphicon glyphicon-ok"></span>'+
                 '保存修改' +
                 '</button>' +*/
                '<button class="btn btn-danger" type="button" data="'+mid+'" onclick="remove_mission(this)">' +
                '<span class="glyphicon glyphicon-trash"></span>' +
                '移除任务' +
                '</button>'
            );
            $(mission).on('mouseover',function(){
                $(this).find('.operate').show();
            }).on('mouseout',function(){
                if(!$(this).find('.save').length)
                    $(this).find('.operate').hide();
            })
            bind_check_change(mission,function(obj){
                var id=$(obj).attr('data');

                if(!$(obj).find('.operate .save').length) {
                    $(obj).find('.operate').prepend(
                        '<button class="btn btn-success save" type="button" data="' + id + '" onclick="save_mission(this)">' +
                        '<span class="glyphicon glyphicon-ok"></span>' +
                        '保存修改' +
                        '</button>'
                    );
                }
            });
        }
    })
}
function toCompleteMission(obj){

}
/**
 * 保存现有的任务
 * @param obj
 * @returns {boolean}
 */
function save_mission(obj){
    //这里的ID是stage的ID，所以，全局查找input[name="mission&'+id+'"]可能会冲突，所以在当前tr里边找
    var id=$(obj).attr('data');
    var mission=$(obj).parents('.mission');//这里是因为更新操作obj之后，通过obj找不到mission。

    //这一块还得留着。。
    var top=$(obj).parents('tr:first');
    var name=top.find('input[name="mission&'+id+'"]');
    var info=top.find('input[name="mission_info&'+id+'"]');
    var hour=top.find('input[name="mission_hour&'+id+'"]');
    var sort=top.find('input[name="mission_sort&'+id+'"]');

    //分开是因为name对象可能后边还有用
    var name_str=name.val();
    var info_str=info.val();
    var hour_str=hour.val();
    var sort_str=sort.val();

    if(!check_mission(mission)){
        return false;
    }
    ajaxOperation(obj,saveMissionUrl,{
        pid:pid,
        mid:id,
        name:name_str,
        info:info_str,
        hour:hour_str,
        sort:sort_str,
        uniqid:operate_uniqid,
    },function(data){
        if(!data.status){
            wq_alert(data.info);
            $(obj).removeAttr('disabled');
            return 0;
        }
        //动态更新验证码，虽然感觉这个没啥用的样子
        if(data.uniqid)
            operate_uniqid=data.uniqid;
        //更新vlaue，否则检测修改可能错误！
        name.attr('value',name_str);
        info.attr('value',info_str);
        hour.attr('value',hour_str);
        sort.attr('value',sort_str);

        $(obj).remove();//更新状态
    });
}
function save_stage(obj){
    //这里的ID是stage的ID，所以，全局查找input[name="mission&'+id+'"]可能会冲突，所以在当前tr里边找
    var id=$(obj).attr('data');
    var stage=$(obj).parents('.stage');//这里是因为更新操作obj之后，通过obj找不到stage。

    //这一块还得留着。。
    var top=$(obj).parents('tr:first');
    var name=top.find('input[name="stage&'+id+'"]');
    var info=top.find('input[name="stage_info&'+id+'"]');
    var sort=top.find('input[name="stage_sort&'+id+'"]');

    //分开是因为name对象可能后边还有用
    var name_str=name.val();
    var info_str=info.val();
    var sort_str=sort.val();


    if(!check_stage($(stage))){
        return false;
    }
    ajaxOperation(obj,saveStageUrl,{
        pid:pid,
        sid:id,
        name:name_str,
        info:info_str,
        sort:sort_str,
        uniqid:operate_uniqid,
    },function(data){
        if(!data.status){
            wq_alert(data.info);
            $(obj).removeAttr('disabled');
            return 0;
        }
        //动态更新验证码
        if(data.uniqid)
            operate_uniqid=data.uniqid;
        //更新vlaue，否则检测修改可能错误！
        name.attr('value',name_str);
        info.attr('value',info_str);
        sort.attr('value',sort_str);

        $(obj).remove();//更新状态
    });
}
/**
 * 保存新的任务
 * @param obj
 * @returns {boolean}
 */
function save_mission_new(obj){
    //这里的ID是stage的ID，所以，全局查找input[name="mission&'+id+'"]可能会冲突，所以在当前tr里边找
    var id=$(obj).attr('data');
    var mission=$(obj).parents('.mission');//这里是因为更新操作obj之后，通过obj找不到mission。

    //这一块还得留着。。
    var top=$(obj).parents('tr:first');
    var name=top.find('input[name="mission&'+id+'"]');
    var info=top.find('input[name="mission_info&'+id+'"]');
    var hour=top.find('input[name="mission_hour&'+id+'"]');
    var sort=top.find('input[name="mission_sort&'+id+'"]');

    //分开是因为name对象可能后边还有用
    var name_str=name.val();
    var info_str=info.val();
    var hour_str=hour.val();
    var sort_str=sort.val();

    /*

    if(name_str.stringLength<MISSION_MIN_NAME||name_str.stringLength>MISSION_MAX_NAME){
        name.parents('.form-group').find('.help').text('任务名长度需要在'+MISSION_MIN_NAME+'到'+MISSION_MAX_NAME+'之间！');
        name.focus();
        return 0;
    }
    if(info_str.stringLength<MISSION_MIN_NAME||info_str.stringLength>MISSION_MAX_NAME){
        info.parents('.form-group').find('.help').text('任务简介长度需要在'+MISSION_MIN_NAME+'到'+MISSION_MAX_NAME+'之间！');
        info.focus();
        return 0;
    }
    if(hour_str<2||hour_str>48){
        hour.parents('.form-group').find('.help').text('学习时长需要在'+2+'到'+48+'之间！');
        hour.focus();
        return 0;
    }
    if(sort_str<0){
        sort.parents('.form-group').find('.help').text('优先级不得小于0');
        sort.focus();
        return 0;
    }*/
    if(!check_mission($(obj).parents('.mission:first'))){
        return false;
    }
    ajaxOperation(obj,addMissionUrl,{
        pid:pid,
        sid:id,
        name:name_str,
        info:info_str,
        hour:hour_str,
        sort:sort_str,
        uniqid:operate_uniqid,
    },function(data){
        if(!data.status){
            wq_alert(data.info);
            $(obj).removeAttr('disabled');
            return 0;
        }
        //动态更新验证码，虽然感觉这个没啥用的样子
        if(data.uniqid)
            operate_uniqid=data.uniqid;

        //任务的id
        var mid=data.mid;

        //更新任务id，以及更新vlaue，否则检测修改可能错误！
        name.attr('data',mid).attr('name','mission&'+mid).attr('value',name_str);
        info.attr('data',mid).attr('name','mission_info&'+mid).attr('value',info_str);
        hour.attr('data',mid).attr('name','mission_hour&'+mid).attr('value',hour_str);
        sort.attr('data',mid).attr('name','mission_sort&'+mid).attr('value',sort_str);
        mission.attr('data',mid);

        //更改按钮组的功能
        $(obj).parents('div.operate:first').html(
           /* '<button class="btn btn-success save" type="button" data="{$val.id}" onclick="save_mission(this)" style="display: none;">' +
            '<span class="glyphicon glyphicon-ok"></span>'+
            '保存修改' +
            '</button>' +*/
            '<button class="btn btn-danger" type="button" data="'+mid+'" onclick="remove_mission(this)">' +
            '<span class="glyphicon glyphicon-trash"></span>' +
            '移除任务' +
            '</button>'
        );
        bind_check_change(mission,function(obj){
            var id=$(obj).attr('data');

            if(!$(obj).find('.operate .save').length) {
                $(obj).find('.operate').prepend(
                    '<button class="btn btn-success save" type="button" data="' + id + '" onclick="save_mission(this)">' +
                    '<span class="glyphicon glyphicon-ok"></span>' +
                    '保存修改' +
                    '</button>'
                );
            }
        });
    });
}
function remove_mission_new(obj){
    wq_confirm('确定删除新增任务？',function(){
        var top=$(obj).parents('tr');
        top.hide('normal',function () {
            $(this).remove();
        });
    });
}
function remove_stage_new(obj){
    wq_confirm('确定删除新增阶段？',function(){
        var top=$(obj).parents('tr');
        top.nextAll().hide('normal',function(){
            $(this).remove();
        });
        top.hide('normal',function () {
            $(this).remove();
        });
    });
}
function remove_mission(obj){
    var id=$(obj).attr('data')
    wq_confirm('确定删除该任务吗？',function(){
        ajaxOperation(obj,removeMissionUrl,{
            pid:pid,
            mid:id,
            uniqid:operate_uniqid,
        },function(data){
            if(!data.status){
                wq_alert(data.info);
                $(obj).removeAttr('disabled');
                return 0;
            }
            //动态更新验证码,可以起到一定的防护作用！
            if(data.uniqid)
                operate_uniqid=data.uniqid;
            var top=$(obj).parents('tr');
            top.hide('normal',function () {
                $(this).remove();
            });
        });
    });
}
function remove_stage(obj){
    var id=$(obj).attr('data')
    wq_confirm('确定删除该阶段吗？',function(){
        ajaxOperation(obj,removeStageUrl,{
            pid:pid,
            sid:id,
            uniqid:operate_uniqid,
        },function(data){
            if(!data.status){
                wq_alert(data.info);
                $(obj).removeAttr('disabled');
                return 0;
            }
            //动态更新验证码,可以起到一定的防护作用！
            if(data.uniqid)
                operate_uniqid=data.uniqid;
            var top=$(obj).parents('tr');

            //寻找他后边的mission一并删除...
            var next=$(top).nextAll();
            for(var i=0;i<next.length;i++){
                if($(next[i]).is('.stage'))
                    break;
                $(next[i]).hide('normal',function(){
                    $(this).remove();
                });
            }

            top.hide('normal',function () {
                $(this).remove();
            });
        });
    });
}
/**
 * 为单选框加上提示信息
 * @param obj
 */
function showTips(obj){
    $(obj).parent().find('.help-block:first').text($(obj).attr('alt'));
}