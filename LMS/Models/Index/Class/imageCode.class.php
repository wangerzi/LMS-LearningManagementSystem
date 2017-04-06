<?php
class imageCode{
    /**
     * @param string $name  session的名称
     * @param int   $length 长度
     * @param int   $mode   类型
     * @param int $width    宽度
     * @param int $height   高度
     * @param int $frame    帧数
     * @param int $fontsize 字体大小
     * @return bool
     */
    function create($name='verify',$length=4, $mode=1, $width = 75, $height = 25,$frame = 64,$fontsize=6,$fontface='simhei.ttf')
    {
        $mode = intval($mode)%5;
        $authstr = $this::randString($length,$mode);
        $_SESSION[$name] = $authstr;        //放入session

        $board_width = $width;
        $board_height = $height;
        $imagedata=array();

        $left = $width/mb_strlen($authstr,'utf-8');
        $top = ($height-$fontsize*2)/4;
        // 生成一个固定帧的GIF动画
        for($i = 0; $i < $frame; $i++)
        {
            ob_start();
            $image = imagecreate($board_width, $board_height);
            imagecolorallocate($image, 0,0,0);
            // 设定文字颜色数组
            $colorList[] = imagecolorallocate($image, 15,73,210);
            $colorList[] = imagecolorallocate($image, 0,64,0);
            $colorList[] = imagecolorallocate($image, 0,0,64);
            $colorList[] = imagecolorallocate($image, 0,128,128);
            $colorList[] = imagecolorallocate($image, 27,52,47);
            $colorList[] = imagecolorallocate($image, 51,0,102);
            $colorList[] = imagecolorallocate($image, 0,0,145);
            $colorList[] = imagecolorallocate($image, 0,0,113);
            $colorList[] = imagecolorallocate($image, 0,51,51);
            $colorList[] = imagecolorallocate($image, 158,180,35);
            $colorList[] = imagecolorallocate($image, 59,59,59);
            $colorList[] = imagecolorallocate($image, 0,0,0);
            $colorList[] = imagecolorallocate($image, 1,128,180);
            $colorList[] = imagecolorallocate($image, 0,153,51);
            $colorList[] = imagecolorallocate($image, 60,131,1);
            $colorList[] = imagecolorallocate($image, 0,0,0);
            $gray = imagecolorallocate($image, 245,245,245);

            imagefill($image, 0, 0, $gray);
            imagerectangle($image,0,0,$width-1,$height-1,$colorList[mt_rand(0,sizeof($colorList)-1)]);

            if (!is_file($fontface)) {
                $fontface = dirname(__FILE__) . "/" . $fontface;
            }

            for ($k = 0; $k < strlen($authstr); $k++)
            {
                $colorRandom = mt_rand(0,sizeof($colorList)-1);
                $float_top = rand(0,$top/2);
                $float_left = rand($left/3,$left*2/3);
                if($mode == 4)
                    imagettftext($image, $fontsize*2, mt_rand(-3, 3), $left * $k + $float_left, $top + $float_top+15, $colorList[$colorRandom], $fontface, mb_substr($authstr,$k,1,'utf-8'));
                else
                    imagestring($image, $fontsize, $left * $k + $float_left,$top + $float_top, substr($authstr, $k, 1), $colorList[$colorRandom]);
            }

            //雪花
            for ($k = 0; $k < 20; $k++)
            {
                $colorRandom = mt_rand(0,sizeof($colorList)-1);
                imagesetpixel($image, rand()%70 , rand()%15 , $colorList[$colorRandom]);

            }
            // 添加干扰线
            for($k = 0; $k < 3; $k++)
            {
                $colorRandom = mt_rand(0, sizeof($colorList)-1);
                // $todrawline = rand(0,1);
                $todrawline = 1;
                if($todrawline)
                {
                    imageline($image, mt_rand(0, $board_width), mt_rand(0,$board_height), mt_rand(0,$board_width), mt_rand(0,$board_height), $colorList[$colorRandom]);
                }
                else
                {
                    $w = mt_rand(0,$board_width);
                    $h = mt_rand(0,$board_width);
                    imagearc($image, $board_width - floor($w / 2) , floor($h / 2), $w, $h,  rand(90,180), rand(180,270), $colorList[$colorRandom]);
                }
            }
            imagegif($image);
            imagedestroy($image);
            $imagedata[] = ob_get_contents();
            ob_clean();
            ++$i;
        }
        require 'GIFEncoder.class.php';
        $gif = new GIFEncoder($imagedata);
        header('Content-type:image/gif');
        echo $gif->GetAnimation();
        return true;
    }

    /**
     * @param $name         名字
     * @param $value        需要比较的值
     * @param bool $ignore  是否忽略大小写
     * @return bool         比对成功/失败
     */
    static public function check($name,$value,$ignore=true){
        $ans = $_SESSION[$name];
        if($ignore) {
            $ans = mb_strtolower($ans, 'utf-8');
            $value = mb_strtolower($value, 'utf-8');
        }
        if($ans == $value && !empty($ans))//如果不验证$ans是否为空的话，可能会存在验证漏洞。
            return true;
        else
            return false;
    }
    static public function remove($name){
        if(isset($_SESSION[$name]))
            unset($_SESSION[$name]);
        return true;
    }
    /**
     * 产生随机字串，可用来自动生成密码
     * 默认长度6位 字母和数字混合 支持中文
     * @param string $len 长度
     * @param string $type 字串类型
     * 0 字母 1 数字 其它 混合
     * @param string $addChars 额外字符
     * @return string
     */
    static public function randString($len=6,$type='',$addChars='') {
        $str ='';
        switch($type) {
            case 0:
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
                break;
            case 1:
                $chars= str_repeat('0123456789',3);
                break;
            case 2:
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
                break;
            case 3:
                $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
                break;
            case 4:
                $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
                break;
            default :
                // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
                $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
                break;
        }
        if($len>10 ) {//位数过长重复字符串一定次数
            $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
        }
        if($type!=4) {
            $chars   =   str_shuffle($chars);
            $str     =   substr($chars,0,$len);
        }else{
            // 中文随机字
            for($i=0;$i<$len;$i++){
                $str.= mb_substr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1,'utf-8');
            }
        }
        return $str;
    }
}