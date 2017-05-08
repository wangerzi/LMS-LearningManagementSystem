<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/7 0007
 * Time: 下午 9:06
 */
class CommonAction extends Action{
    public function _initialize(){
        if(is_login()){
            if(cookie('user_key')!=calc_user_key(session('uid'),cookie('username'))){
                logout();
                $this->error('用户密匙验证失败！为了您的安全，请重新登录！',U(GROUP_NAME.'/Login/index'),IS_AJAX);
            }
        }
        //验证session_key
        if(!cookie('session_key')) {
            cookie('session_key', get_session_key());//都会重新分配session_key
            if(is_login()) {
                logout();//退出登录！
                $this->error('session口令验证失败，为了您的安全，请重新登录！','',IS_AJAX);
            }
        }
        else{
            if(cookie('session_key')!=get_session_key()){
                logout();
                cookie('session_key',null);
                $this->error('session口令验证失败，为了您的安全，请重新登录！',U(GROUP_NAME.'/Login/index',IS_AJAX));
            }
        }
        //检查需要管理员权限的分组是否有管理员权限，放在前边是为了不被请登录不暴露后台地址
        if(in_array(GROUP_NAME,C('NEED_ADMIN_GROUP'))){
            if(!is_admin())
                _404('页面不存在！');
            //管理员的处理方法
            $this->admin_message_num = admin_message_num();
            $this->feedbackTip = M('feedback')->field('name,time')->where('status IS NULL')->order('time DESC')->limit(4)->select();//只显示一次都没处理过的，即：status IS NULL。
        }
        if(!in_array(GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME,C('NO_LOGIN_ROUTE'))&&!is_login()) {
            session('login_refer',__SELF__);
            $this->error('请先登录！', U(GROUP_NAME . '/Login/index'));
        }
        //把控制器名称传入作为left_row的默认值，前端自动激活相关选项。
        $this->module_name=MODULE_NAME;
        //echo pillStr('这是一个未完成的计划123',C('PLAN_MIN_NAME'),C('PLAN_MAX_NAME'));
        //实例化user，签到信息的完善
        if(is_login()) {
            $uid = session('uid');
            $this->user = M('user')->field(array('id', 'username', 'face' , 'checkout' , 'exp', 'email', 'info'))->find($uid);
            //p($arr_num);
        }
    }
    //为某控制器初始化表单验证码。
    /**
     * @param null $control
     * @param string $prefix    当需要初始化多个表单验证码的时候，可通过加前缀来解决。
     * @param array $cache_clean   清除uniqid的时候会自动清除缓存，这里是模块名，控制器名，方法名的数组。
     * @return bool
     */
    protected function initUniqid($control=null,$prefix='',$cache_clean=array()){
        $control=empty($control)?GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME:$control;
        //相当于不带后缀，不带HTML_PATH的路径。
        $cache_clean=get_cache_file_name($cache_clean);
        $uniqid=get_uniqid();
        session($control.'_uniqid',$uniqid);

        //增加缓存以后新增的
        session($control.'_cache_clean',$cache_clean);

        //保存生成随机码
        $name = $prefix.'url_uniqid';
        $this->$name=$uniqid;
        $name = $prefix.'uniqid';
        $this->$name=$uniqid;

        //快捷生成表单的字符串。
        $name = $prefix.'__UNIQID__';
        $this->$name="<input type='hidden' name='uniqid' value='{$uniqid}'/>";
        return true;
    }
    //标识码验证处理！
    protected function checkFormUniqid($uniqid,$control=null){
        if(!checkFormUniqid($uniqid,$control))
            $this->error('表单唯一标识码不正确，为了您的安全，请刷新重试！');
    }
    //标识码验证处理！
    protected function checkUrlUniqid($uniqid,$control=null){
        if(!checkFormUniqid($uniqid,$control))
            $this->error('页面唯一标识码不正确，为了您的安全，请刷新重试！'.session('Index/Login/forgetHandle_uniqid'));
    }
}