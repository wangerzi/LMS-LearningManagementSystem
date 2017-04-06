/**
 * Created by Administrator on 2017/4/4.
 */
function deleteUser(obj){
    var url = $(obj).attr('alt');
    $('#userConfSure').attr('href',url);
    $('#userConf').modal('open');
}
function activeUser(obj){
    var url = $(obj).attr('alt');
    $('#userActiveConfSure').attr('href',url);
    $('#userActiveConf').modal('open');
}
function ipDetail(obj){
    var detail = $('#ipDetail');
    get_ip_addr(detail.find('.modal-content'),$(obj).text());
    detail.modal('open');
}
function get_ip_addr(obj,ip){
    var api = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip='+ip;
    $.ajax({
        url     :   api,
        type    :   'get',
        dataType:   'script',//跨域
        success :   function(){
            var str = '<h4>IP 详细信息</h4>'+'<p>IP：'+ip+'</p>';
            var data = remote_ip_info;
            if(!remote_ip_info) {
                $(obj).html('<h4>接口申请失败</h4><p>IP:' + ip + '</p><p>错误：' + text + '&' + error + '</p><p>该地址可能是私网地址或不合法地址</p>');
                return ;
            }
            if(data.country)
                str += '<p>国家：'+data.country+'</p>';
            if(data.province)
                str += '<p>省份：'+data.province+'</p>';
            if(data.city)
                str += '<p>城市：'+data.city+'</p>';
            if(data.district)
                str += '<p>区：'+data.district+'</p>';
            if(data.isp)
                str += '<p>ISP：'+data.isp+'</p>';
            if(data.type)
                str += '<p>类型：'+data.type+'</p>';
            if(data.desc)
                str += '<p>其他：'+data.desc+'</p>';
            $(obj).html(str);
            remote_ip_info = false;//清空掉，以免出现其他情况。
        },
        error   :   function(xml,text,error){
            $(obj).html('<h4>调用接口失败</h4><p>IP:'+ip+'</p><p>错误：'+xml.statusText+'&'+text+'&'+error+'</p><p>该地址可能是私网地址或不合法地址</p>');
        }
    });
}