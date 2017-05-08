<?php
    class userModel extends Model {
        //配置自动验证
        protected $_validate = array(
            //array('username','3,12','用户名长度不符合规则',Model::MUST_VALIDATE,'length'),
            array('username','','用户名重复',Model::MUST_VALIDATE,'unique'),
            array('username','/^[\x{4e00}-\x{9fa5}a-zA-z1-9_]*$/u','用户名只能是中文、英文、数字、下划线',Model::MUST_VALIDATE,'regex'),
            //array('password','6,20','密码长度不符合规则',Model::MUST_VALIDATE,'length'),
            array('password','password_2','两次输入密码不匹配！',Model::MUST_VALIDATE,'confirm'),
            array('email','email','邮箱不符合规范',Model::MUST_VALIDATE),
            array('email','','邮箱已被注册',Model::MUST_VALIDATE,'unique'),
        );
        //配置自动完成。
        protected $_auto=array(
            array('password','calc_password',Model::MODEL_INSERT,'function'),
            array('reg_time','time',Model::MODEL_INSERT,'function'),
            array('last_time','time',Model::MODEL_BOTH,'function'),
            array('login_ip','get_client_ip',Model::MODEL_BOTH,'function'),
        );
    }
?>