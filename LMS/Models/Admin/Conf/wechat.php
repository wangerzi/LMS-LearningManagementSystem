<?php
/**
 * Created by PhpStorm.
 * User: 94468
 * Date: 2017/10/23
 * Time: 9:01
 */
echo 11111111;
return array(
    'wechat_option'=>[
        'debug'  => true,
        'app_id' => 'wx2d57c1c356465038',
        'secret' => '466ce6ee8a7df16c9566ce80fa008db9',
        'token'  => 'Jeffrey',
        'aes_key' => 'gJts6IWWqYvGXE0elA0mJeELevu5WEGT8aUUe7xW0s6', // 可选
        'log' => [
            'level' => 'debug',
            'file'  => '/tmp/easywechat.log', // XXX: 绝对路径！！！！
        ],
    ],
    // 关键字回复
    'WECHAT_KEYWORDS'   =>  [],
    // 默认回复
    'WECHAT_DEFAULT_REPLY'  =>  '',
    // 关注时回复
    'WECHAT_SUBCRIBE_CONTENT'   =>  '',
);