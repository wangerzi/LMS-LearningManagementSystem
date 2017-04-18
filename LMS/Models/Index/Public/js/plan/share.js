/**
 * Created by Administrator on 2016/11/23 0023.
 */
$(function() {
    $("#main-body").removeClass('col-md-11').addClass('col-md-12').parent().removeClass('col-md-10').addClass('col-md-12');
    //调用datepicker插件
    $('.input-daterange').datepicker({
        format: "yyyy-mm-dd",
        language:'zh-CN',
        //autoclose: true,
        startDate: today,
        todayHighlight: true
    });
    //验证码刷新
    $('.verify').click(function(){
        $(this).attr('src',function(i,oldVal){return oldVal+'&r='+Math.random()});
    });

    //根据是否已赞，调整策略！
    if (praised == '1')
        $('#praise-btn').attr('disabled', 'disabled').find('span.praise').text('已赞');
    else
        $('#praise-btn').click(function () {
            $('#praise-btn').attr('disabled', 'disabled');
            $.ajax({
                url: praiseUrl,
                type: 'post',
                data: {
                    uniqid: urlUniqid,
                    pid: pid
                },
                success: function (data) {
                    wq_alert(data.text);
                    if (!data.status) {
                        $('#praise-btn').removeAttr('disabled');
                        return 0;
                    }
                    $('#praise-btn').attr('disabled', 'disabled').find('span.praise').html('已赞');
                    $('#praise-btn').find('span.num').text(
                        function (index, oldVal) {
                            return parseInt(oldVal) + 1;
                        }
                    );
                },
                error: function (status, xml, statusText) {
                    wq_alert(statusText + '，请检查网络！');
                }
            });
        });

    //表单验证
    $('#joinForm').bootstrapValidator({
        verbose: false,
        message: 'This value is not valid',
        feedbackIcons:{
            /*验证状态用的图标！*/
            valid:'glyphicon glyphicon-ok',
            invalid:'glyphicon glyphicon-remove',
            validating:'glyphicon glyphicon-refresh'
        },
        fields:{
            start:{
                validators:{
                    notEmpty:{
                        message:'开始时间不能为空！'
                    },
                    date:{
                        format:'YYYY-MM-DD',
                        message:'时间格式不匹配'
                    },
                    callback:{
                        message:'开始时间不能在今天以前！',
                        callback:function(value,validator){
                            var m = new moment(value, 'YYYY-MM-DD', true);
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isAfter(yesterday);//'2000-01-01' 形式的！
                        }
                    }
                }
            },
            end:{
                validators:{
                    notEmpty:{
                        message:'结束时间不能为空！'
                    },
                    date:{
                        format:'YYYY-MM-DD',
                        message:'时间格式不匹配'
                    },
                    callback:{
                        message:'结束时间不能在开始时间以前！',
                        callback:function(value,validator){
                            var m = new moment(value, 'YYYY-MM-DD', true);
                            if (!m.isValid()) {
                                return false;
                            }
                            // Check if the date in our range
                            return m.isAfter($('input[name="start"]').val());//'2000-01-01' 形式的！
                        }
                    }
                }
            },
            verify:{
                validators:{
                    notEmpty:{
                        message:'验证码不能为空',
                    },
                    stringLength:{
                        min:verifyLen,
                        max:verifyLen,
                        message:'验证码长度应该为'+verifyLen,
                    },
                    remote:{
                        url:verifyCheckUrl,
                        message:'验证码不正确',
                        delay:1000,
                        data:function(){
                            return {
                                verify:$("input[name='verify']").val(),
                            };
                        },
                        type:'post',
                    }
                }
            }

        }
    }).on('success.form.bv',function(e){
        e.preventDefault();

        var form = $('#joinForm');

        wq_confirm('确定要加入此计划吗？<br/>注：您不能修改此计划中的内容，只能完成设定好的任务。',function(){
            submitForm(form,function(data){
                wq_alert('加入成功');
            },{pid:pid})
        });
    });
});