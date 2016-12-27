/**
 * Created by Administrator on 2016/12/22 0022.
 */
//检阅进度
function checkSchedule(obj){
    var id=$(obj).attr('data');
    if($('input[name="check&'+id+'"]').length){
        $(obj).parents('tr').next().hide('normal',function(){
            $(this).remove();
        });
    }else{
        $(obj).parents('tr:first').after(
            '<tr class="check-schedule" style="display: none;"><td colspan="3">' +
            '   <div style="font-size:50px;" class="text-success col-md-5 starArea">' +
            '       <span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="做的太差，还需努力 ╮(╯▽╰)╭"></span>' +
            '       <span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="辣眼睛 (＠￣ー￣＠)"></span>' +
            '       <span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="还可以 (∩_∩)"></span>' +
            '       <span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="大赞 (～￣▽￣)～"></span>' +
            '       <span style="cursor:pointer;" class="glyphicon glyphicon-star-empty" alt="无法形容的好o(≧v≦)o~~"></span>' +
            '   </div>' +
            '   <div class="col-md-7 text-warning starInfo" style="line-height:50px;"></div>'+
            '   <div class="input-group col-md-12">' +
            '       <input type="hidden" class="starInput" name="check_star&'+id+'" />' +
            '       <input class="form-control checkInput" type="text" name="check&'+id+'" placeholder="输入对这次表现的评价吧：" />' +
            '       <div class="input-group-btn">' +
            '           <button class="btn btn-success commit" data="'+id+'" onclick="submitCheck(this)">提交</button>' +
            '       </div>' +
            '   </div>' +
            '</td></tr>'
        );
        var new_tr=$(obj).parents('tr').next().show('normal');
        new_tr.find('.checkInput').keypress(function(e){
            if(e.which==13)
                new_tr.find('.commit').click();
        });
        new_tr.find('.starArea span').click(function(e){
            $(this).removeClass('glyphicon-star-empty').addClass('glyphicon-star');
            $(this).prevAll().removeClass('glyphicon-star-empty').addClass('glyphicon-star');
            $(this).nextAll().removeClass('glyphicon-star').addClass('glyphicon-star-empty');
            new_tr.find('.starInfo').text($(this).attr('alt'));
            new_tr.find('.starInput').val($(this).prevAll().length+1);
        }).eq(2).click();
    }
}
function submitCheck(obj){
    var id=$(obj).attr('data');
    var content=$('input[name="check&'+id+'"]').val();
    var len=content.stringLength;
    if(len==0||len>230){
        wq_alert('您的评价不能为空，或者大于240字！');
        return 0;
    }
    ajaxOperation(obj,submitCheckUrl,{
        sid:id,
        star:$('input[name="check_star&'+id+'"]').val(),
        content:    content,
        uniqid :    uniqid,
    },function(data){
        if(!data.status){
            wq_alert(data.text);
            $(obj).removeAttr('disabled');
            return 0;
        }
        $(obj).parents('tr').prev().find('.checkSchedule').click().removeAttr('onclick').attr('disabled','disabled').text('已检阅');
    })
}