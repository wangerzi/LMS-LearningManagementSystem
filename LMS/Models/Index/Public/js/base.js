/**
 * Created by Administrator on 2016/10/30 0030.
 */
$(function(){
    //激活collapase折叠插件;
    //$('.collapse').collapse();
    $('#left-leader').find("li[alt='"+left_row+"']").addClass('active');

    //签到的ajax获取
    $.ajax({
        url     :   checkoutInitUrl,
        dataType:   'json',
        type    :   'post',
        success :   function(data){
            if(!data.status) {
                wq_alert('数据获取失败' + data.info);
                return 0;
            }
            $('#checkoutArea').html(data.info);
            /*进度条效果*/
            $('#exp-bar').animate({
                width:parseFloat($("#exp-bar").attr('alt'))*100+'%',
            },'fast');
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
        },
        error   :   function(){
            $('#checkoutArea').html('数据获取失败');
        }
    });
    //气泡的获取
    $.ajax({
        url         :   popUrl,
        type        :   'post',
        dataType    :   'json',
        success     :   function(data){
            if(!data.status){
                wq_alert(data.info);
                return 0;
            }
            if(data.num.friend>0)
                $('.number-friend').text(data.num.friend);
            if(data.num.supervision>0)
                $('.number-supervision').text(data.num.supervision);
            if(data.num.message>0)
                $('.number-message').text(data.num.message);
        },
        error       :   function(status,xml,statusText){
            wq_alert('气泡获取失败，'.statusText);
        }
    });

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