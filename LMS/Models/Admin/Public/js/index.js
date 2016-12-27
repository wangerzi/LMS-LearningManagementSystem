/**
 * Created by Administrator on 2016/10/30 0030.
 */
$(function(){
    $("#banner dl dd a,#left-banner dl dd a").click(function(){
        $("#right-content").attr("src",$(this).attr("alt"));
    });
});