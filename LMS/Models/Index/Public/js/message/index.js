/**
 * Created by Administrator on 2016/12/4 0004.
 */
$(function(){
});
function showMessageDetail(obj){
    $(obj).text('收起').attr('onclick','hideMessageDetail(this)');
    var content=$(obj).prev();

    content.html(function(index,oldVal){
        return oldVal.replace('\n','<br>');
    });
    var height=content.css('display','inline').height();
    content.removeAttr('style');
    if(height<40)
        return;

    content.animate({
        height:height,
    },'slow');
}
function hideMessageDetail(obj){
    $(obj).text('显示详情').attr('onclick','showMessageDetail(this)');
    var content=$(obj).prev();

    content.html(function(index,oldVal){
        return oldVal.replace('<br>','\n');
    });
    content.stop(true,false).animate({
        height:40,
    },'slow');
}
/**
 * 阅读信息的ajax函数
 * @param obj
 */
function readMessage(obj){
    $(obj).attr('disabled','disabled');
    var id=$(obj).attr('data');
    $.ajax({
        url:readMessageUrl,
        type:'post',
        dataType:'json',
        data:{
            id:id,
            uniqid:url_uniqid,
        },
        success:function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).text('已阅读');
            $('.number-message').text(function(i,val){
                return parseInt(val)-1;
            });
        },
        error:function(status,xml,statusText){
            wq_alert(statusText+'，可能服务器忙，请重试！');
            $(obj).removeAttr('disabled');
        }
    })
}
/**
 * 删除信息的函数！
 * @param obj
 */
function deleteMessage(obj){
    wq_confirm('确定要删除这封私信吗？',function(){
        $(obj).attr('disabled','disabled');
        var id=$(obj).attr('data');
        $.ajax({
            url:deleteMessageUrl,
            type:'post',
            dataType:'json',
            data:{
                id:id,
                uniqid:url_uniqid,
            },
            success:function(data){
                if(!data.status){
                    wq_alert(data.text);
                    $(obj).removeAttr('disabled');
                    return 0;
                }
                $(obj).parents('tr:first').animate({
                    left:200,
                    opacity:0.2,
                },'slow',function(){
                    $(this).remove();
                });
            },
            error:function(status,xml,statusText){
                wq_alert(statusText+'，可能服务器忙，请重试！');
                $(obj).removeAttr('disabled');
            }
        })
    },'提示','danger');
}
function deleteAll(obj){
    wq_confirm('真的要清空私信吗？这将无法撤销！',function(){
        location.href=$(obj).attr('data');
    });
}