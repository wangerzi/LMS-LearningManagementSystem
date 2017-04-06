<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/25 0025
 * Time: 下午 8:50
 */
class RegisterAction extends CommonAction
{
    public function index(){
        //是否开启注册。
        if(!C('REG_OPEN'))
            $this->error('管理员已关闭注册！');
        $this->initUniqid(GROUP_NAME.'/Register/register');
        $this->display();
    }
    public function register()
    {
        if (!IS_POST)
            _404("页面不存在");

        //是否开启注册。
        if (!intval(C('REG_OPEN')))
            $this->error('管理员已关闭注册！');

        //唯一标识码验证。
        if (!checkFormUniqid(I('post.uniqid')))
            $this->error("表单唯一标识码不匹配，为了您的账号安全，请刷新重试！", U('index'));

        //验证码验证
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        if(intval(C('REG_VERIFY')) && !imageCode::check('register_verify',I('post.verify')))
            $this->error('验证码错误！');

		$parse = C('TMPL_PARSE_STRING');
        $data = array(
            'username' => I("post.username"),
            'email' => I("post.email"),
            'password' => I('post.password'),
            'password_2' => I('post.password_2'),
        );
		if(in_array($data['username'],C('FORBID_NAME'))){
			$this->error('敏感用户名不能注册！');
		}
        $db = D('user');

        if (!$user = $db->create())
            $this->error($db->getError());
        else {
			$user['face'] = $parse['__PUBLIC__'].'/images/faces/default.png';//放在data里被过滤了。
            $birth = strtotime(I('post.birth'));
            if($birth > time())
                $this->error('生日需要在今天之前');
            $user['birth']=$birth;
            $user['reg_time'] = time();
            //加入数据库，或者添加失败日志。
            if (!$user = M('user')->add($user)) {
                add_log("用户{$data['username']}加入数据库失败");
                $this->error('加入数据库失败！');
            }

            $active = get_uniqid();

            //严格方式的加密：!$db->save(array('id'=>$user,'password'=>calc_password($user,I("post.password"))))||
            if (!M('active')->add(array('user_id' => $user, 'active' => $active,))) {
                add_log("激活码入库出错，用户{$data['username']}({$data['user_id']})注册被取消！");
                M('user')->delete($user);
                $this->error('激活码入库出错，请重新注册！');
            }
            /*创建用户的文件夹*/
            $dir=APP_PATH.'data/user/'.$user.'/images/';
            if(!mkdir($dir,0777,true) && !is_dir($dir)){//递归创建目录，在目录创建失败并且不存在
                //echo '用户私有空间创建失败！';
				$db->delete($user);
				$this->error("注册失败：用户私有空间创建失败，请联系管理员配置服务器权限！位置：".$dir);
			}

            session("reg_verify", null);//销毁验证码！
            $info=array(
                'uid'   =>  $user,
                'rem_evd_time' =>  get_time(0)+9*3600,
                'rem_warn_time' => get_time(0)+16*3600,
            );
            if(!M('user_config')->add($info)) {
                $db->delete($user);
                rmdir($dir);
                $this->error('默认配置入库失败，请重试！');
            }

            //清除标识码。
            clearUniqid();
            //发送邮件
            $this->sendRegisterEmail($data['email'],$data['username'],$active);
            $this->success("注册成功，请前往邮箱激活！", U(GROUP_NAME . "/Login/index"));//注册成功的跳转。
        }
    }
    public function checkEmail(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');

        $arr=array(
            'valid' =>  false,
        );

        $email=I('post.email');
        $db=M('user');
        $map=array(
            'email' =>  array('eq',$email),
        );

        $user=$db->where($map)->find();
        if(empty($user))
            $arr['valid']=true;
        $this->ajaxReturn($arr);
    }
    public function checkUsername(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $data=array(
            'valid' =>  false,
        );
        $name=I('post.username');
        $uid=session('uid');//无论是否登录。
        $db=M('user');

        load('@/check');

        $data['valid']=checkRepeatUsername($name,$uid);
       /* $data['last_sql']=$db->getLastSql();
        $data['is_admin']=is_admin();*/
        $this->ajaxReturn($data);
    }
    /*验证码检测*/
    public function verifyCheck(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $verify=I('post.verify');
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $valid = imageCode::check('register_verify',$verify);
        $this->ajaxReturn(array('valid'=>$valid));
    }
    /*生成验证码方法！*/
    public function verify(){
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $img = new imageCode();
        $img->create('register_verify',C('VERIFY_LEN'),C('VERIFY_TYPE'),100,30);
    }
    public function active(){
        if(!isset($_GET['active']))
            _404('页面不存在！');
        $active=I('get.active');
		if(!ctype_alnum($active)){
			echo '只能是字母或数字的组合';
			die();
		}
        if($data=M('active')->where("active='%s'",$active)->find()) {
            if(M('active')->delete($data['id'])){
                if(is_login())
                    $url = '';
                    //$url = U('Admin/User/index');
                else
                    $url = U(GROUP_NAME.'/Login/index');
                $this->success('激活成功！',$url);
            }
            else
                $this->error('未知原因，激活失败，请刷新重试！');
        }
        else{
			//p(M('active')->getLastSql());
			//die();
            $this->error('激活码不存在！');
		}
    }
    /*发送注册邮件的方法*/
    private function sendRegisterEmail($email,$username,$active){
        load('@/email');
        $url=U(GROUP_NAME.'/Register/active',array('active' => $active),false,false,true);

        //模板替换
        $content=file_get_contents(C('MAIL_TPL').'regMail.html');
        $content=str_replace('{__USERNAME__}',$username,$content);
        $content=str_replace('{__URL__}',$url,$content);
        if(!addEmailTimeQueue($email,$username,C('WEB_NAME').'的注册邮件',$content,0)){
			$this->error('注册邮件发送失败，请反馈管理员！');
		}
    }
}