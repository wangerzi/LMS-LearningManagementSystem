/**
 * Created by Administrator on 2016/11/23 0023.
 */
$(function() {
    $("#main-body").removeClass('col-md-11').addClass('col-md-12').parent().removeClass('col-md-10').addClass('col-md-12');

    /*replaceWord($("input[name='title']"),'回复主题');
    replaceWord($("textarea[name='content']"),'请输入您想评论的内容');*/
});
/**
 * 制造相关表单
 * @param obj
 * @param withStar
 */
function fastComment(obj,withStar){
   /* scrollTo('#commentWarp');
    $("input[name='rid']").val($(obj).attr('data'));
    if($(obj).attr('alt')!='') {
        replaceWord($("textarea[name='content']"), '回复' + $(obj).attr('alt') + '的评论：');
    }else{
        replaceWord($("textarea[name='content']"),'请输入您想评论的内容');
    }*/
   //动态创建有利于减少数据传输
   var content=$('input[name="content&'+$(obj).attr('data')+'"]');
   if(content.length>0){
       //隐藏并删除提交框
       $(obj).parent().next().hide('normal',function(){
           $(obj).parent().next().remove();
       });
   }else {
       //生成一个表单
       $(obj).parent().after(
           '<div style="display: none;">' +
               '<div class="input-group col-md-12">' +
                   '<input name="content&' + $(obj).attr('data') + '" placeholder="评论'+$(obj).attr('alt')+'：" class="form-control" value="" />' +
                   '<div class="input-group-btn">' +
                   '<button class="btn btn-success submit-btn" data="' + $(obj).attr('data') + '" onclick="submitThis(this)">提交</button>' +
                   '</div>' +
               '</div>' +
           '</div>'
       );
       //如果带星星的话，就多写点
       if(withStar){
           $(obj).parent().next().prepend(
               '<div style="font-size:50px;" class="text-success col-md-5" id="starArea">' +
               '<span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="万年难得一见的差 ╮(╯▽╰)╭"></span>' +
               '<span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="辣眼睛 (＠￣ー￣＠)"></span>' +
               '<span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="还可以 (∩_∩)"></span>' +
               '<span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="大赞 (～￣▽￣)～"></span>' +
               '<span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="无法形容的好o(≧v≦)o~~"></span>' +
               '</div>' +
               '<div class="col-md-7 text-warning" id="starInfo"></div>'
           );
           $('#starArea span').click(function(e){
               $(this).removeClass('glyphicon-star-empty').addClass('glyphicon-star');
               $(this).prevAll().removeClass('glyphicon-star-empty').addClass('glyphicon-star');
               $(this).nextAll().removeClass('glyphicon-star').addClass('glyphicon-star-empty');
               $('#starInfo').text($(this).attr('alt'));
               $('#starInput').val($(this).prevAll().length+1);
           }).eq(2).click();
       }
       $(obj).parent().next().show('normal');
       //重新获取对象
       content=$('input[name="content&'+$(obj).attr('data')+'"]');
       content.keypress(function(e){
           if(e.which==13)
               content.next().find('.submit-btn').click();
       })
   }
}
/**
 * 显示子评论
 * @param obj
 */
function showChildComment(obj){
    var id='#'+$(obj).attr('data')+'_comments';
    $(id).stop(true,false).toggle('normal');
}
/**
 * 提交评论
 * @param obj
 * @returns {number}
 */
function submitThis(obj){
    //实例化对象
    var content=$('input[name="content&'+$(obj).attr('data')+'"]');

    var onclick=$(obj).attr('onclick');
    $(obj).attr('disabled','disabled').attr('onclick','');
    var len=content.val().length;
    if(len>230||len<1) {
        wq_alert('评论不得为空或超过230字！');
        $(obj).removeAttr('disabled').attr('onclick',onclick);
        return 0;
    }
    $.ajax({
        url:comment_url,
        type:'post',
        dataType:'json',
        data:{
            rid:$(obj).attr('data'),
            pid:pid,
            star:$('#starInput').val(),
            content:content.val(),
            uniqid:comment_uniqid,
        },
        success:function(data){
            if(!data.status) {
                wq_alert(data.text,function(){
                    $(obj).removeAttr('disabled').attr('onclick',onclick);
                });
                return 0;
            }
            wq_alert('评论成功，刷新后生效');
            //评论成功后清空content，收起评论框
            //$(obj).removeAttr('disabled').attr('onclick',onclick);
            content.val('');
            $(obj).parent().parent().parent().prev().find('.btn').click();
        },
        error:function(xml,text){
            wq_alert(text+' 服务器君可能再忙，请稍后重试！');
            $(obj).removeAttr('disabled').attr('onclick',onclick);
        }
    });
}