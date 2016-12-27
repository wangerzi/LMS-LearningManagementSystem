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
        if(!is_login()&&in_array(GROUP_NAME,C('NEED_ADMIN_GROUP'))&&!is_admin()){
            _404('页面不存在！');
        }
        if(!in_array(GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME,C('NO_LOGIN_ROUTE'))&&!is_login()) {
            cookie('login_refer',__SELF__);
            $this->error('请先登录！', U(GROUP_NAME . '/Login/index'));
        }
        //把控制器名称传入作为left_row的默认值，前端自动激活相关选项。
        $this->module_name=MODULE_NAME;
        //echo pillStr('这是一个未完成的计划123',C('PLAN_MIN_NAME'),C('PLAN_MAX_NAME'));
        //实例化user，签到信息的完善
        if(is_login()) {
            $uid=session('uid');
            $this->user = M('user')->field(array('id', 'username', 'face' , 'checkout' , 'exp'))->find($uid);
            $this->checkout=array(
                'total' => M('checkout')->where("uid='%d' AND time > '%d'",session('uid'),get_time(0))->count(),
                'serialize' => $this->user['checkout'],
            );
            //查找匹配的等级！
            $level=M('level')->where("need < '%d'",$this->user['exp'])->order('level DESC')->limit(1)->select();
            if(empty($level)){//如果没找到，那就默认一个。
                $level=array(
                    'level' => 0,
                    'need'  => 0,
                    'plan_num'=> 4,
                    'exp'   => 2,
                );
            }else{
                $level=$level[0];
            }
            //找出离用户经验最近的下一等级
            $tmp=M('level')->where('need > %d',$this->user['exp'])->order('need ASC')->limit(1)->select();
            if(empty($tmp)){
                //这种情况在满级的时候能看到！
                $level['next']=0;
                $level['next_need']=$this->user['exp'];
            }else{
                $level['next']=$tmp[0]['need']-$this->user['exp'];
                $level['next_need']=$tmp[0]['need'];
            }
            $this->level=$level;

            //对需要提示的数字进行统计
            $arr_num=array(
                'message'   =>  M('message')->where("rid='%d' AND status=0",$uid)->count(),
                'friend'    =>  M('friend_request')->where("rid='%d'",$uid)->count(),
                //监控这里只需要统计未处理的申请就好了！
                'supervision'=> M('supervision_request')->where("rid='%d' AND status=0",$uid)->count()+M('supervision_log')->table(C('DB_PREFIX').'supervision_log AS sv')->join(C('DB_PREFIX').'plan_clone AS pc ON sv.pcid=pc.id AND sv.status<>1')->where("pc.svid='%d'",$uid)->count(),
                //'plan_all'  =>  M('plan_clone')->where("uid='%d'",$uid)->count(),加上个徽标之后空间不够了。。。
            );
            $this->number=$arr_num;
            //p($arr_num);
        }
    }
    //为某控制器初始化表单验证码。
    protected function initUniqid($control=null){
        $control=empty($control)?GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME:$control;
        $uniqid=get_uniqid();
        session($control.'_uniqid',$uniqid);


        $this->url_uniqid=$uniqid;
        $this->uniqid=$uniqid;

        //快捷生成表单的字符串。
        $this->__UNIQID__="<input type='hidden' name='uniqid' value='{$uniqid}'/>";
        return true;
    }
    //标识码处理！
    protected function checkFormUniqid($uniqid,$control=null){
        if(!checkFormUniqid($uniqid,$control))
            $this->error('表单唯一标识码不正确，为了您的安全，请刷新重试！');
    }
}