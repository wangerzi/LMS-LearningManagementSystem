/**
 * Created by Administrator on 2016/10/30 0030.
 */
$(function(){
    $("input[level='1'],input[level='2']").click(function(){
        var inputs=$(this).parents('dl:first').find("input[type='checkbox']");

        if($(this).is(":checked"))
            inputs.prop("checked",true);
        else
            inputs.prop("checked",false);
    });
});