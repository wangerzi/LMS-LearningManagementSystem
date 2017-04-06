<?php
/**
 *更新配置文件，将$dst数组中的与原配置文件中匹配的$key覆盖，如果原配置文件中类型为int，更新后类型还会是数字。
 * @param $path
 * @param $dst
 * @param string $filter
 * @return array
 */
function updateConf($path, $dst, $filter='htmlspecialchars'){

    $data=array(
        'status'    =>  false,
        'info'      =>  '',
    );
    if(!file_exists($path) || !is_array($dst)){
        $data['info']   = '配置文件不存在或目标不是数组';
        return $data;
    }
    $arr=include $path;
    if(!is_array($arr)){
        $data['info']   =  '配置文件返回的不是数组';
        return $data;
    }

    //判断filter是否存在。
    if(function_exists($filter))
        $is_func = true;
    else
        $is_func = false;
    $new = array();

    foreach($arr as $key => $val){
        if(!isset($dst[$key])) {
            $data['info'] = '表单元素缺失'.$key;
            return $data;
        }

        $v = $is_func?$filter($dst[$key]) : $dst[$key];

        if(is_numeric($val)){
            //如果目标是数字，但上传可能不是数字，则判断上传是否为on，最后再进行floatval()转换。
            $v = $v == 'on'?1:doubleval($v);
        }
        $new[$key]=$v;
    }
    //更新文件。
    if(F(pathinfo($path, PATHINFO_FILENAME),$new,dirname($path).'/')) {
        $data['status'] = true;
        return $data;
    }
    else {
        $data['info'] = '更新失败，请尝试给予' . $path . '权限';
        return $data;
    }
}
?>