/**
 * Created by Administrator on 2016/10/30 0030.
 */
$(function(){
    $('#left-leader').find("li[alt='"+left_row+"']").addClass('active');
    /*签到的提交按钮*/
    $('#checkout').click(function(){
        $.ajax({
            url         :   checkoutUrl,
            dataType    :   'json',
            type        :   'post',
            success     :   function(data){
                $('#checkoutArea').html(data.text);
            },
            error       :   function(status,xml,statusText){
                $('#checkoutArea').html(statusText);
            }
        });
    });
    $('#exp-bar').animate({
        width:parseFloat($("#exp-bar").attr('alt'))*100+'%',
    },'fast');

    //问候。
    say_hello('span#title_hello');

    //将需要用到的对象转换出来！
    var left_leader=$('#left-leader');
    var footer=$('#footer');
    var leaderPic=$('#leaderPic');

    //在距离底部多远停下！
    var hover=20;
    $(window).scroll(function(){
        var top=$(document).scrollTop();
        var max=$(document).height()-footer.height()-left_leader.height()-leaderPic.height()-hover;
        if(top<max&&top>0){
            left_leader.stop(true,false).animate({'top':(top-100)>0?top-100:0},'slow');
        }
    });

    /*to-top部分的功能实现区*/
    $('.scroll-top')
        .find('li')
        .mouseenter(function(){$(this).find('.alt').stop(true,false).show('fast')})
        .mouseleave(function(){$(this).find('.alt').stop(true,false).hide('fast')});
    $(window).scroll(function(){
        var top=$(document).scrollTop();
        if(top < 150)
            $('#to-top').stop().slideUp('noraml');
        else
            $('#to-top').stop().slideDown('noraml');
    });
});