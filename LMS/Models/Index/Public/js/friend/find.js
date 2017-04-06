/**
 * Created by Administrator on 2016/11/29 0029.
 */
$(function(){
    var contentObj=$("input[name='content']");
    replaceWord(contentObj,'请输入姓名或邮箱');
    contentObj.keypress(function(e){
        if(e.which==13)
            $('#search').click();
    });
    $('#search').click(function(){
        var form=$('#search-form');
        var result=$('#result');
        var len=$(form).find("input[name='content']").val().length;


        if(len>FRIEND_SEARCH_MAX||len<FRIEND_SEARCH_MIN){
            wq_alert('搜索内容长度需要在'+FRIEND_SEARCH_MIN+'字到'+FRIEND_SEARCH_MAX+'字之间');
            contentObj.blur();
            return 0;
        }
        $(this).attr('disabled','disabled');
        loadToResult(result,$(form).attr('action'),{
            uniqid  :   $(form).find("input[name='uniqid']").val(),
            content :   $(form).find("input[name='content']").val()
        });
        return false;
    });
});