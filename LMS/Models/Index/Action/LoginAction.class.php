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
        if(!IS_POST)
            _404('页面不存在！');
		if(is_login())
			$this->ajaxReturn(array('valid'=>true,'info'=>'您已登录','location' => U(GROUP_NAME.'/Index/index') ));
        if(!checkFormUniqid(I('post.uniqid')))
            $this->error('登录提交唯一标识码不匹配，请刷新后重试！');
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        if(intval(C('LOGIN_VERIFY')) && !imageCode::check('login_verify',I('post.verify')))
            $this->error('验证码错误！');
        $db=D('user');
        $user=$db->where('username="%s" or email="%s"',I('post.username'),I('post.username'))->find();
        if(!empty($user)) {
            $res=M('active')->where('user_id=%d',$user['id'])->limit(1)->select();
            if(count($res)>0)
                $this->error('用户未激活，请前往注册邮箱激活!');
            if($user['password']!=calc_password(I('post.password')))
                $this->error('密码不正确！');
            if($user['lock'])
                $this->error('用户已被锁定');
            
            //清空表单验证码
            clearUniqid();
            //清理验证码。
            imageCode::remove('login_verify');

            //设置cookie
            session('uid',$user['id']);
            cookie('user_key',calc_user_key($user['id'],$user['username']));
            cookie('username',$user['username']);
            session('is_login',true);

            //验证是否是管理员。
            $res=M('admin')->field('level')->where('uid=%d',$user['id'])->find();
            if(count($res)>0) {
                session(C('ADMIN_TAG'), true);
                session('ADMIN_LEVEL', $res['level']);
            }
            //更新最近登录时间和登录IP
            $map = array(
                'id'        =>  $user['id'],
                'last_time' =>  time(),
                'login_ip'  =>  get_client_ip(),
            );
            $db->save($map);

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
            $refer=session('login_refer');
			$data=array(
                    'info'  =>  '登录成功，自动跳至登录前所在地',
                    'status'=>  1,
                );
            //在为空或者是主页的情况下，直接跳转，否则提示跳转并清除session.
            if(!empty($refer)&&$refer!=U(GROUP_NAME.'/Index/index','',true,false,true)){
                //redirect(U(GROUP_NAME . '/Index/index'));
				$data['location']=$refer;
                //$this->success('登录成功，自动跳转至登录前所在地');
            }
            session('login_refer',null);
			$this->ajaxReturn($data);
        }
        else
            $this->error('用户名或密码错误！');
    }
    /*用户名检测的AJAX处理*/
    public function usernameCheck(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $username=I('post.username');
        $map=array(
            'username'  =>  array('eq',$username),
            'email'     =>  array('eq',$username),
            '_logic'    =>  'or',
        );
        $user=M('user')->field('id,username')->where($map)->find();
        if(empty($user))
            $this->ajaxReturn(array('valid'=>false));
        else
            $this->ajaxReturn(array('valid'=>true));
    }
    /*密码检测的ajax处理*/
    public function pwdCheck(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $username=I('post.username');
        $pwd=I('post.xxx');
        $map=array(
            'username'  =>  array('eq',$username),
            'email'     =>  array('eq',$username),
            '_logic'    =>  'or',
        );
        $user=M('user')->field('id,password,username')->where($map)->find();
        if(empty($user)||$user['password']!=calc_password($pwd))
            $this->ajaxReturn(array('valid'=>false));
        else
            $this->ajaxReturn(array('valid'=>true));
    }
    /*验证码检测*/
    public function verifyCheck(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $verify=I('post.verify');
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $valid = imageCode::check('login_verify',$verify);
        $this->ajaxReturn(array('valid'=>$valid));
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
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $img = new imageCode();
        $img->create('login_verify',C('VERIFY_LEN'),C('VERIFY_TYPE'),100,30);
       /* import('ORG.Util.Image');
        if(C('VERIFY_TYPE')==4)
            Image::GBVerify(C('VERIFY_LEN'),'png',100,30,'simhei.ttf','login_verify');
        else
            Image::buildImageVerify(C('VERIFY_LEN'),C('VERIFY_TYPE'),'png',100,30,'login_verify');*/
    }
    public function forget(){
        $this->initUniqid(GROUP_NAME.'/Login/forgetHandle');
        $this->display();
    }
    public function forgetHandle(){
        if(!IS_POST){
            _404('页面不存在！');
        }
        $name = I('post.username');
        $map=array(
            'username'  =>  array('eq',$name),
            'email'     =>  array('eq',$name),
            '_logic'    =>  'or',
        );
        $user=M('user')->field('id,email')->where($map)->find();
        if(empty($user))
            $this->error('用户不存在'.$name);
        session('uid',$user['id']);
        //调用那个已存在的方法即可。
        $account = A('Index/Account');
        $account->passwordSetNew();
    }
    /**
     * 发送设置新邮箱的邮箱验证码！
     */
    function sendEmailCode(){
        if(!IS_POST||!IS_AJAX){
            _404('页面不存在！');
        }

        $data=array(
            'status'    =>  false,
            'text'      =>  '',
        );
        //检查标识码
        $this->checkUrlUniqid(I('get.uniqid'),GROUP_NAME.'/Login/forgetHandle');
        $name = I('post.name');
        $map=array(
            'username'  =>  array('eq',$name),
            'email'     =>  array('eq',$name),
            '_logic'    =>  'or',
        );
        $user=M('user')->field('id,email')->where($map)->find();
        if(empty($user))
            $this->error('用户不存在'.$name);
        //检查是否过期
        $space = time()-session('_mailCode_time');
        if($space < 60)
            $this->error('一分钟之内只能请求一次，现在还差'.(60-$space).'s的冷却时间');
        $email = $user['email'];
        $uid = $user['id'];
        //记录请求时间
        session('_mailCode_time',time());

        load('@/code');
        $code=createCode();
        //发送并保存邮箱验证码
        saveCode($uid,$code,GROUP_NAME.'/Login/forgetHandle');
        sendCode($email,$code);
        $data['status']=true;
        $this->ajaxReturn($data);
    }

    /**
     * 检查邮箱验证码
     */
    function emailCodeCheck(){
        if(!IS_POST||!IS_AJAX){
            _404('页面不存在！');
        }
        $name = I('post.name');
        $map=array(
            'username'  =>  array('eq',$name),
            'email'     =>  array('eq',$name),
            '_logic'    =>  'or',
        );
        $user=M('user')->field('id,email')->where($map)->find();
        if(empty($user))
            $this->error('用户不存在'.$name);
        $uid=$user['id'];
        $code=I('post.code');

        $arr=array('valid'=>false);
        load('@/code');
        $for=GROUP_NAME.'/Login/forgetHandle';

        //检查码是否正确
        if(checkCode($uid,$code,$for,false)==0){
            $arr['valid']=true;
        }
        $arr['user'] = $user;
        /*$arr['lastSql']=M('code')->getLastSql();
        $arr['code']=checkCode($uid,$code,$control,false);*/
        $this->ajaxReturn($arr);
    }
}