/**
 * Created by wang on 17-3-30.
 */
$(function(){
    var conf = {
        start: [MIN_NAME, MAX_NAME],
        connect: true,
        step: 1,
        range: {
            'min': 0,
            'max': 15
        },
        tooltips:[true,true],
    };
    //名字长度范围。
    var name =$('#nameLen')[0];
    noUiSlider.create(name, conf);
    conf['start'] = [MIN_PAS, MAX_PAS];
    conf['range'] = {'min':4, 'max':20};
    var pwd = $('#pwdLen')[0];
    noUiSlider.create(pwd, conf);
    conf['start'] = [MIN_YEAR,MAX_YEAR];
    conf['range'] = {'min':1900, 'max':2017};
    var year = $('#yearLen')[0];
    noUiSlider.create(year,conf);

    //在数据改变的时候，改变隐藏域的值
    name.noUiSlider.on('update',function(){
        var arr = this.get();
        $('input[name="MIN_NAME"]').val(arr[0]);
        $('input[name="MAX_NAME"]').val(arr[1]);
    });
    pwd.noUiSlider.on('update',function(){
        var arr = this.get();
        $('input[name="MIN_PAS"]').val(arr[0]);
        $('input[name="MAX_PAS"]').val(arr[1]);
    });
    year.noUiSlider.on('update',function(){
        var arr = this.get();
        $('input[name="MIN_YEAR"]').val(arr[0]);
        $('input[name="MAX_YEAR"]').val(arr[1]);
    });
    /*var form = $('input[name="REG_OPEN"]').parents('form:first');
    form.submit(function(e){
        //获取数据。
        var arr = $('#nameLen').find('.noUi-tooltip');
        var MIN_NAME = arr[0].text();
        var MAX_NAME = arr[1].text();

        arr = $('pwdLen').find('.noUi-tooltip');
        var MIN_PAS = arr[0].text();
        var MAX_PAS = arr[1].text();
        $(form).append('<input name="MIN_NAME" value="' + MIN_NAME + '" />');
        $(form).append('<input name="MIN_NAME" value="' + MAX_NAME + '" />');
        $(form).append('<input name="MIN_NAME" value="' + MIN_PAS + '" />');
        $(form).append('<input name="MIN_NAME" value="' + MAX_PAS + '" />');
        return true;
    });*/
});