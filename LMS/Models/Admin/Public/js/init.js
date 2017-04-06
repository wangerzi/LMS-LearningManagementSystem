/**
 * Created by Administrator on 2017/2/24.
 */
$(function(){
    //初始化组件
    $('.button-collapse').sideNav();
    $('select').material_select();
    $('.modal').modal();

    //顺畅滑动。
    $('.scroll-to').click(function(e){
        e.preventDefault();
        var target;
        if($(this).is('a'))
            target = $(this).attr('href');
        else
            target = $(this).attr('data-target');
        target = $(target).first();
        if(target.length)
            $('body,html').animate({scrollTop:target.offset().top});
    });
    //toTop的实现。
    $(window).scroll(function(){
        var top = $(document).scrollTop();
        var toTop = $('#to-top');
        if(top > 40)
            toTop.fadeIn();
        else
            toTop.fadeOut();
    });
    var footer = $('footer:first');
    var main = $('main:first');
    //优化页脚的显示
    if($(main).height() < $(window).height() - $(footer).height() - 64) {
        $(footer).css('margin-top', $(window).height() - $(main).height());
        //$(footer).css({position:'fixed'});
    }
});
/**
 * 设置默认值，通过判断是否是null的方式判断是否传参，来返回原值或设置的默认值。
 * @param {Object} input
 * @param {Object} val
 */
function defaultValue(input,val){
    if(input == null)
        return val;
    else
        return input;
}
/**
 * 切换某两个类。
 * @param {Object} obj
 * @param {Object} class1
 * @param {Object} class2
 */
function switchClass(obj,class1,class2){
    class1=defaultValue(class1,'scale-in');
    class2=defaultValue(class2,'');
    if($(obj).is('.'+class1)){
        $(obj).removeClass(class1).addClass(class2);
    }else{
        $(obj).removeClass(class2).addClass(class1);
    }
}
function wq_alert($message){

}