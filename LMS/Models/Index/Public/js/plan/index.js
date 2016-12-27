/**
 * Created by Administrator on 2016/11/27 0027.
 */
function delete_plan(obj){
    wq_confirm("确认要删除这个计划吗？<br>注：公开任务可以从分享页里重新添加，但学习进度将丢失！",function(){
        ajaxOperation(obj,deletePlanUrl,{
            uniqid:uniqid,
            pcid:$(obj).attr('data'),
        },function(data){
            if(!data.status){
                wq_alert(data.info);
                $(obj).removeAttr('disabled');
                return 0;
            }
            if(typeof data.uniqid != 'undefined')
                uniqid=data.uniqid;
            $(obj).parents('.plan_detail').hide('normal',function(){
                $(this).remove();
            });
        })
    });
}