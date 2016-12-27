<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/7 0007
 * Time: 下午 9:06
 */
class ServerAction extends CommonAction{
    public function index(){
        $data=array(
            //服务器主机名称
            '服务器主机名称' =>$_SERVER['SERVER_NAME'],
            //当前浏览IP
            '当前浏览IP'   =>$_SERVER['REMOTE_ADDR'],
            //服务器使用端口
            '服务器使用端口' => $_SERVER['SERVER_PORT'],
            //连接使用端口
            '连接使用端口' => $_SERVER['REMOTE_PORT'],
            //网站使用路径
            '网站使用路径' => $_SERVER['PATH_TRANSLATED'],
            //HTTPS
            'HTTPS' => $_SERVER['HTTPS']?'是':'否',
        );
        $this->data=$data;
        $this->display();
    }
}