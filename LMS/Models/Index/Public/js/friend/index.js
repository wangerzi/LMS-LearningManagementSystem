/**
 * Created by Administrator on 2016/11/29 0029.
 */
$(function () {
    var result=$('#result');
    //初始化此页！
    $('#result-leader').find('li a').click(function(){
        activeLeader(this);
        loadThis(this,result);
    }).eq(0).click();//默认点击第一个！
});