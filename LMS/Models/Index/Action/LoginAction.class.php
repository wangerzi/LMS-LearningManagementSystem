<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/7 0007
 * Time: 下午 9:06
 */
class LoginAction extends CommonAction{
    public function index(){
        $this->initUniqid(GROUP_NAME.'/Login/loginHandle');
        if(is_login())
            $this->error('您已登录',U(GROUP_NAME.'/Index/index'));

        $this->display();
    }
    public function loginHandle(){
        if(!IS_POST||is_login())
            _404('页面不存在！');
        if(!checkFormUniqid(I('post.uniqid')))
            $this->error('登录提交唯一标识码不匹配，请刷新后重试！');
        if(C('LOGIN_VERIFY')&&md5(I('post.verify'))!=session('login_verify'))
            $this->error('验证码错误！');
        $db=D('user');
        $user=$db->where('username="%s" or email="%s"',I('post.username'),I('post.username'))->limit(1)->select();
        $user=$user[0];//定位到1.
        if($user) {
            $res=M('active')->where('user_id=%d',$user['id'])->limit(1)->select();
            if(count($res)>0)
                $this->error('用户未激活，请前往注册邮箱激活!');
            if($user['password']!=calc_password(I('post.password')))
                $this->error('密码不正确！');

            //在thinkPHP的自动完成里边已经自动更新数据了。


            //设置cookie
            session('uid',$user['id']);
            cookie('user_key',calc_user_key($user['id'],$user['username']));
            cookie('username',$user['username']);
            session('is_login',true);

            session('login_verify',null);//使用后清空验证码，防止重复提交。

            //验证是否是管理员。
            $res=M('admin')->where('uid=%d',$user['id'])->limit(1)->select();
            if(count($res)>0)
                session(C('ADMIN_TAG'),true);
            //清空验证码
            clearUniqid();

            //处理info表缺失的异常
            $db_user_info=M('user_config');
            $map=array(
                'uid'   =>  array('eq',$user['id']),
            );
            $info=$db_user_info->where($map)->limit(1)->select();
            if(empty($info)){
                $info=array(
                    'uid'   =>  $user['id'],
                    'rem_evd_time' =>  strtotime(get_time(0).' 9:00'),
                    'rem_warn_time' =>  strtotime(get_time(0).' 16:00'),
                );
                $db_user_info->add($info);
            }

            //自动跳转到登录之前的页面
            $refer=cookie('login_refer');
            //在为空或者是主页的情况下，直接跳转，否则提示跳转并清除cookie.
            if(empty($refer)||$refer==U(GROUP_NAME.'/Index/index')){
                cookie('login_refer',null);
                redirect(U(GROUP_NAME . '/Index/index'));
            }else{
                cookie('login_refer',null);
                $this->success('登录成功，自动跳转至登录前所在地',$refer);
            }
        }
        else
            $this->error('用户名或密码错误！');
    }
    public function logout(){
        if(is_login()) {
            logout();
            $this->success('成功退出登录！',U(GROUP_NAME.'/Login/index'));
        }
        else
            $this->error('您还没有登录！',U(GROUP_NAME.'/Login/index'));
    }
    public function verify(){
        import('ORG.Util.Image');
        if(C('VERIFY_TYPE')==4)
            Image::GBVerify(C('VERIFY_LEN'),'png',180,50,'simhei.ttf','login_verify');
        else
            Image::buildImageVerify(C('VERIFY_LEN'),C('VERIFY_TYPE'),'png',180,50,'login_verify');
    }
}