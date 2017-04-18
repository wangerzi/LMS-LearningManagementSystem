/**
 * Created by Administrator on 2016/11/23 0023.
 */
$(function() {
    replaceWord($('#studyContent'),'写点记录吧....');
    $("#main-body").removeClass('col-md-11').addClass('col-md-12').parent().removeClass('col-md-10').addClass('col-md-12');
    unlock($('.plan-lock:first'));

    $('#studyContent').keyup(function(e){
        var len=$(this).val().length;
        var last=240-len;
        last=last>0?last:0;
        $(this).next().find('span').text(last);
    });
    $(window).keydown(function(e){
        if(e.which==27){
            $('#complete_mission').find('.cancle').click();
        }
    });
    //根据是否已赞，调整策略！
    if (praised == '1')
        $('#praise-btn').attr('disabled', 'disabled').find('span.praise').text('已赞');
    else
        $('#praise-btn').click(function () {
            $('#praise-btn').attr('disabled', 'disabled');
            $.ajax({
                url: praiseUrl,
                type: 'post',
                data: {
                    uniqid: urlUniqid,
                    pid: pid
                },
                success: function (data) {
                    wq_alert(data.text);
                    if (!data.status) {
                        $('#praise-btn').removeAttr('disabled');
                        return 0;
                    }
                    $('#praise-btn').attr('disabled', 'disabled').find('span.praise').html('已赞')
                        .find('span.num').text(
                        function (index, oldVal) {
                            return parseInt(oldVal) + 1;
                        }
                    );
                },
                error: function (status, xml, statusText) {
                    wq_alert(statusText + '，请检查网络！');
                }
            });
        });
    //进度条动画
    obj=$('.progress .progress-bar:first');
    obj.animate({
        width:parseFloat(obj.attr('alt')) + '%',
    }, 'fast');
});
function complete_plan(obj){
    $('body').append('<div class="alert-background"></div>');
    var complete_mission_obj=$('#complete_mission');
    //展示
    complete_mission_obj.parent().show();

    complete_mission_obj
        .animate({
            marginTop:'15%',
            opacity:1,
        },'normal')
        .find('.sure')
        .unbind('click')
        .click(function(){
            var id=$(obj).attr('data');
            var next=$(obj).parentsUntil('row').find('.disabled:first');
            var info=$('#studyContent').val();
            var len=utf8_length(info);
            $(this).attr('disabled','disabled');

            //sure的对象。
            var sure=this;
            if(len<1||len>240) {
                wq_alert('内容不得为空，或超过240字');
                return 0;
            }
            /*将自己的class改变，然后将下一个解锁*/
            $.ajax({
                url: completeUrl,
                dataType:'json',
                type:'post',
                data:{
                    id:  id,
                    info:info,
                },
                success:function(data){
                    if(!data.status) {
                        wq_alert(data.text);
                        $(sure).removeAttr('disabled');
                        return 0;
                    }
                    wq_alert('成功完成该任务，获得'+data.exp+'经验！');
                    $('#studyContent').val('');
                    complete_mission_obj.parent().hide();
                    $('body').find(".alert-background:last").remove();
                    $(sure).removeAttr('disabled');//移除disabled的效果。
                    /*改变自己的样式！*/
                    $(obj).removeClass('btn-warning')
                        .addClass('btn-success')
                        .removeAttr('onclick')
                        .html('<span class="glyphicon glyphicon-ok"></span> 已完成')
                        .after(' <button class="btn btn-default">'+
                            '<span class="glyphicon glyphicon-time"></span>'+
                            data.time+
                            '</button>')
                    ;
                    //只有在列表模式下，才会解锁下一个。
                    /*解锁下一个！*/
                    if(mode==1)
                        unlock(next);
                },
                error:function(status,xml,statusText){
                    wq_alert(statusText+'，可能服务器忙，请稍后重试！');
                    $(sure).removeAttr('disabled');
                },
            })
        });
    complete_mission_obj.find('.cancle')
        .unbind('click')
        .click(function(){
        $('#complete_mission').animate({
            marginTop:0,
            opacity:0,
        },'normal',function(){
            $(this).css('margin-top','60%');
            $('body').find(".alert-background:last").remove();
            complete_mission_obj.parent().hide();
        });
    });
}
function unlock(obj){
    $(obj).removeClass('disabled')
        .addClass('btn-warning')
        .html('<span class="glyphicon glyphicon-screenshot"></span> 待完成')
        .attr('onclick','complete_plan(this)');
}