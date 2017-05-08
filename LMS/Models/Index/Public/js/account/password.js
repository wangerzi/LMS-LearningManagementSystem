/**
 * Created by Administrator on 2017/4/24.
 */
$(function(){
    replaceWord($("input[name='password']"),'请输入原密码');
    $('#submit').click(function(e){
        e.preventDefault();

        //获取form表单
        var form=$('#pwdSubmit');

        $('#submit').attr("disabled",'disabled');

        //加密
        var pas = $('input[name="password"]');
        var pwd_before = pas.val();
        pas.val(hex_sha1(pwd_before));
        //ajax提交
        $(form).ajaxSubmit({
            url:form.attr('action'),
            dataType:'json',
            type:'post',
            success:function(data) {
                if(!data.status){
                    pas.val(pwd_before);//恢复以前的密码。
                    wq_alert(data.info);
                    $('#submit').attr("disabled",'disabled');//禁用按钮，除非有改动。
                    return 0;
                }
                if(data.location!=null)
                    location.href=data.location;
                else
                    location.href=successUrl;
            },
            error:function(xml,text){
                pas.val(pwd_before);//恢复以前的密码。
                wq_alert(text+'可能服务器忙，请稍后重试！');
                $('#submit').removeAttr('disabled');
                return 0;
            }
        });
    });
});