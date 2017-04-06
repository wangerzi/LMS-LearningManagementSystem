/**
 * Created by Administrator on 2016/12/22 0022.
 */
function penalize(obj){
    wq_confirm('您真的要对他实施鞭笞吗？<br>系统将发送一封鞭笞邮件给被计划执行者，每天每计划限一次！',function(){
        var id=$(obj).attr('data');
        ajaxOperation(obj,penalizeUrl,{
            pcid    :   id,
            uniqid  :   uniqid
        },function(data){
            if(!data.status){
                wq_alert(data.text);
                $(obj).removeAttr('disabled');
                return 0;
            }
            wq_alert('鞭笞邮件已发送');
            $(obj).text('已鞭笞');
        });
    },'警告','danger');
    return 0;
    //需要自己说两句吗？
    if($('input[name="penalize&'+id+'"]').length){
        $(obj).parents('.thumbnail').find('.form-group').hide('normal',function(){
            $(this).remove();
        });
    }else{
        $(obj).parents('.thumbnail').append(
            '<div class="form-group" style="display: none;">' +
            '   <div class="input-group">' +
            '       <input class="form-control" type="text" name="penalize&'+id+'" placeholder="说点狠话刺激TA一下吧：" />' +
            '       <div class="input-group-btn">' +
            '           <button class="btn btn-danger" type="button" data="'+id+'" onclick="penalizeSubmit(this)">鞭笞TA</button>' +
            '       </div>' +
            '   </div>' +
            '</div>'
        ).find('.form-group').show('normal');
    }
}
/**
 * 提交信息
 * @param obj
 */
function penalizeSubmit(obj){
    var id=$(obj).attr('data');
    var content=$('input[name="penalize&'+id+'"]').val();
    var len=content.stringLength;
    if(len<1||len>20){
        wq_alert('鞭笞');
        return 0;
    }
    ajaxOperation(obj,penalizeUrl,{
        pcid    :   id,
        uniqid  :   uniqid
    });
}