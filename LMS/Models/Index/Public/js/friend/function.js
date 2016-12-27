/**
 * Created by Administrator on 2016/11/29 0029.
 */
function loadThis(obj,result){
    $(obj).attr('disabled','disabled');
    var addr=$(obj).attr('alt');
    loadToResult(result,addr);
    $(obj).removeAttr('disabled');
}
function loadToResult(result,addr,data){
    $(result).html('<div class="alert alert-info">' +
        '<span class="glyphicon glyphicon-refresh active"></span>' +
        ' <span class="loading">加载中，请稍后...</span>' +
        '</div>');
    $.ajax({
        url : addr,
        type: 'post',
        dataType:'html',
        data:data,
        success:function(data){
            $(result).html(data);
        },
        error:function(status,xml,statusText){
            $(result).find('.loading').html(statusText+'加载失败，请重试！');
        }
    })
}
/**
 * 发送一个添加朋友的请求
 * @param obj
 */
function addFriend(obj){
    $(obj).attr('disabled','disabled');
    var uid=$(obj).attr('data');
    $.ajax({
        url:addFriendUrl,
        type:'post',
        dataType:'json',
        data:{
            uid:uid,
            fate:fate,
            uniqid:url_uniqid,
        },
        success:function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).html('<span class="glyphicon glyphicon-ok"></span> ' +
                ' 已申请');
        },
        error:function(status,xml,statusText){
            wq_alert(statusText+'请重试！');
            $(obj).removeAttr('disabled');
        }
    })
}
function passRequest(obj){
    $(obj).attr('disabled','disabled');
    var rid=$(obj).attr('data');
    $.ajax({
        url:passFriendUrl,
        type:'post',
        dataType:'json',
        data:{
            rid:rid,
            uniqid:url_uniqid
        },
        success:function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).html('<span class="glyphicon glyphicon-ok"></span> ' +
                ' 已通过');
            $('.number-friend').text(function(i,val){
                return parseInt(val)-1;
            });
        },
        error:function(status,xml,statusText){
            wq_alert(statusText+'请重试！');
            $(obj).removeAttr('disabled');
        }
    })
}
function rejectRequest(obj){
    $(obj).attr('disabled','disabled');
    var rid=$(obj).attr('data');
    $.ajax({
        url:passFriendUrl,
        type:'post',
        dataType:'json',
        data:{
            rid:rid,
            uniqid:url_uniqid
        },
        success:function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).parent().parent().parent().fadeOut('fast',function(){$(this).remove()})
        },
        error:function(status,xml,statusText){
            wq_alert(statusText+'请重试！');
            $(obj).removeAttr('disabled');
        }
    })
}
//激活导航条！
function activeLeader(obj){
    $(obj).parent().siblings().removeClass('active');
    $(obj).parent().addClass('active');
}
function deleteFriend(obj){
    wq_confirm('确定删除这个好友吗？这将不可恢复！',function(){
        var fid=$(obj).attr('data');
        $(obj).attr('disabled','disabled');
        var rid=$(obj).attr('data');
        $.ajax({
            url:deleteFriendUrl,
            type:'post',
            dataType:'json',
            data:{
                fid:fid,
                uniqid:url_uniqid
            },
            success:function(data){
                if(!data.status){
                    wq_alert(data.text);
                    $(obj).removeAttr('disabled');
                    return 0;
                }
                $(obj).parent().parent().parent().fadeOut('fast',function(){$(this).remove()})
            },
            error:function(status,xml,statusText){
                wq_alert(statusText+'请重试！');
                $(obj).removeAttr('disabled');
            }
        })
    });
}