<?php

/**
 * @Author: 94468
 * @Date:   2017-10-30 11:31:47
 * @Last Modified by:   94468
 * @Last Modified time: 2017-10-30 12:43:02
 */
use EasyWeChat\Message\Text;
/**
* WechatLogic 处理微信逻辑
*/
class WechatLogic
{
	/**
	 * easywechat的application
	 * @param [type] $app [description]
	 */
	function __construct($app)
	{
		$this->app = $app;
	}
	/**
	 * 处理事件和消息
	 * @param  [type] $message [description]
	 * @return [type]          [description]
	 */
	function messageHandle($message){
		if( $message->MsgType == 'event'){
            switch ($message->Event) {
                case 'subscribe':
                    $ans = new Text(['content' => C('WECHAT_SUBCRIBE_CONTENT')]);
                    break;
                default:
                    // $ans = new Text(['content' => C('WECHAT_DEFAULT_REPLY')]);
                    break;
            }
        } elseif( $message->MsgType == 'text'){
            // 处理关键词匹配
            $keywords = C('WECHAT_KEYWORDS');
            foreach ($keywords as $key => $value) {
            	$keys = explode(',', $key);
            	// 匹配keywords
            	foreach($keys as $k){
            		if(false !== strpos($message->Content, trim($k))){
            			$ans = new Text(['content' => $value]);
            			break 2;
            		}
            	}
            }
        }
		return isset($ans) ? $ans : new Text(['content' => C('WECHAT_DEFAULT_REPLY')]);
	}
}