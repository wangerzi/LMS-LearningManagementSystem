<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/16 0016
 * Time: 下午 12:39
 */
function get_user_file($file,$uid=null){
    if(is_null($uid))
        $uid=session('uid');
    return C('USER_BASE_PATH').$uid.'/'.$file;
}

/**
 * 保存用户图片到其自身的image文件夹，注意宽高与thumbPrefix对应。
 * @param null $uid
 * @param string $thumbMaxWidth
 * @param string $thumbMaxHeight
 * @param string $thumbPrefix
 * @return array
 */
function save_user_image($uid=null,$thumbMaxWidth='900,262',$thumbMaxHeight='500,148',$thumbPrefix='m_,s_'){
    if(is_null($uid))
        $uid=session('uid');

    $data=array(
        'status'    =>  false,
    );

    import('ORG.Net.UploadFile');
    $upload = new UploadFile();
    $upload->maxSize = 3292200;
    $upload->allowTypes = explode(',', 'image/jpg,image/jpeg,image/gif,image/png');
    $upload->savePath = APP_PATH . 'data/user/'.$uid.'/images/';
    if(!is_dir($upload->savePath))
        if(!mkdir($upload->savePath,0777,true)){
        $data['info'] = '目录不存在，并且创建失败。'.$upload->savePath;
        return $data;
        }
    //缩略图路径
    //$upload->thumbPath = APP_PATH . 'data/user/'.$uid.'/images/';
    // 设置引用图片类库包路径
    $upload->imageClassPath = 'ORG.Util.Image';
    //开启缩略图！
    $upload->thumb=true;
    //设置需要生成缩略图的文件后缀
    $upload->thumbPrefix = $thumbPrefix;  //默认生产2张缩略图
    //设置缩略图最大宽度
    $upload->thumbMaxWidth = $thumbMaxWidth;
    //设置缩略图最大高度
    $upload->thumbMaxHeight = $thumbMaxHeight;
    //设置上传文件规则
    $upload->saveRule = 'time';
    //删除原图
    $upload->thumbRemoveOrigin = true;
    if (!$upload->upload()) {
        //捕获上传异常
        $data['info']=$upload->getErrorMsg();
        //echo $upload->getErrorMsg();
    } else {
        //取得成功上传的文件信息
        $uploadList = $upload->getUploadFileInfo();
        if(C('PLAN_WATER_OPEN')) {
            import("ORG.Util.Image");
            //给m_缩略图添加水印, Image::water('原文件名','水印图片地址')
            Image::water($uploadList[0]['savepath'] . $uploadList[0]['savename'], C('PLAN_WATER_ADDR'));
        }
        $data['status']=true;
        $data['path'] = __ROOT__.'/'.$uploadList[0]['savepath'].$uploadList[0]['savename'];
    }
    return $data;
}