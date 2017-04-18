/**
 * Created by wang on 17-3-30.
 */
$(function(){
    var conf = {
        start: [PLAN_MIN_NAME, PLAN_MAX_NAME],
        connect: true,
        step: 1,
        range: {
            'min': 0,
            'max': 25
        },
        tooltips:[true,true]
    };
    //计划名字长度范围。
    var plan =$('#planLen')[0];
    noUiSlider.create(plan, conf);
    conf['start'] = [STAGE_MIN_NAME, STAGE_MAX_NAME];
    conf['range'] = {'min':0, 'max':45};
    var stage = $('#stageLen')[0];
    noUiSlider.create(stage, conf);
    conf['start'] = [MISSION_MIN_NAME,MISSION_MAX_NAME];
    conf['range'] = {'min':0, 'max':45};
    var mission = $('#missionLen')[0];
    noUiSlider.create(mission,conf);

    //在数据改变的时候，改变隐藏域的值
    plan.noUiSlider.on('update',function(){
        var arr = this.get();
        $('input[name="PLAN_MIN_NAME"]').val(arr[0]);
        $('input[name="PLAN_MAX_NAME"]').val(arr[1]);
    });
    stage.noUiSlider.on('update',function(){
        var arr = this.get();
        $('input[name="STAGE_MIN_NAME"]').val(arr[0]);
        $('input[name="STAGE_MAX_NAME"]').val(arr[1]);
    });
    mission.noUiSlider.on('update',function(){
        var arr = this.get();
        $('input[name="MISSION_MIN_NAME"]').val(arr[0]);
        $('input[name="MISSION_MAX_NAME"]').val(arr[1]);
    });
});