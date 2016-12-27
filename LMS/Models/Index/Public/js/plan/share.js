/**
 * Created by Administrator on 2016/11/23 0023.
 */
$(function() {
    $("#main-body").removeClass('col-md-11').addClass('col-md-12').parent().removeClass('col-md-10').addClass('col-md-12');

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
});