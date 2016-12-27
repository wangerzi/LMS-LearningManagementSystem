/**
 * Created by Administrator on 2016/12/4 0004.
 */
$(function () {
    var face_info=$('#edit-face');
    $('#face-warp').mouseenter(function(){
        face_info.stop(true,false).slideDown('fast');
    }).mouseleave(function(){
        face_info.stop(true,false).slideUp('fast');
    });
});