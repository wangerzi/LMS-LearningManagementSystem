/**
 * Created by Administrator on 2016/12/21 0021.
 */
//解除监督
function removeSv(obj){
    wq_confirm('确认解除监督关系吗？这将无法撤销！',function(){
        ajaxOperation(obj,removeUrl,{
            req_id  :   $(obj).attr('data'),
            uniqid  :   uniqid,
        },function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).parents('tr:first').hide('normal',function(){
                $(this).remove();
            });
        });
    });
}
//拒绝监督
function refuseSv(obj){
    wq_confirm('确认拒绝此监督请求吗？',function(){
        ajaxOperation(obj,refuseUrl,{
            req_id  :   $(obj).attr('data'),
            uniqid  :   uniqid,
        },function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).parents('tr:first').hide('normal',function(){
                $(this).remove();
            });
        });
    },'提示','danger');
}
//同意监督
function agreeSv(obj){
    wq_confirm('确认同意此监督计划吗？<br>好友的学习进度将会以站内信的方式发送给您，而您可以给好友的学习表现评分！',function(){
        ajaxOperation(obj,agreeUrl,{
            req_id  :   $(obj).attr('data'),
            uniqid  :   uniqid,
        },function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).text('操作成功');
        });
    });
}
//被人抢先之后删除。
function delSv(obj){
    wq_confirm('确认删除此监督请求吗？',function(){
        ajaxOperation(obj,delUrl,{
            req_id  :   $(obj).attr('data'),
            uniqid  :   uniqid,
        },function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            $(obj).parents('tr:first').hide('normal',function(){
                $(this).remove();
            });
        });
    },'提示','danger');
}