<?php

/*
  20170407 加入定期全刷，减少越来越黑的问题
  20170407 读取电池电量显示在右下角
  20170407 加入standby处理，减少功耗
  20170408 待机功能交给sleep脚本处理，不在这里处理
  20170415 增加菜单显示功能  author:wushy(qq:10249082)
  20170417 不同的书使用不同的进度
  20170420 增加可配置功能
  20170426 GBK支持,修正连续英文显示的问题，混合填充取为-1 
 */

/*
 * 20170407 
 * 更新换书检查逻辑 增加欢迎页面 另加了一个debug逻辑 方便浏览器调试 
 * 减小文字，增大页面显示容量
 * 修改换书逻辑 
 * bugfix：
 * 半角字符导致页面显示不整齐
 */
define("APP", "ebook");
define("APP_BASE", dirname(__FILE__) . "/");
if (!defined('DEBUG')) {
    define("DEBUG", 0);
}
include(APP_BASE . "/../system/inkcase5.inc.php");
################################################################################
# 以下可配置项，可在config.ini中修改，请尽量不要修改代码
################################################################################
define("PADDING", app_config(APP, "四周留空", 10)); //四周留空
define("FONT", (DEBUG == 0) ? app_config(APP, "字体文件", "/opt/qte/fonts/msyh.ttf") : "msyh.ttf");

define("BOOK_SELECTED", '/mnt/udisk/ebook/current_book');
define("MENU_COUNT", '/tmp/menu_count');
define("MENU_STATUS", '/tmp/menu_status');
#全局进度变量
global $g_book_var;
#全局显示模式
global $g_show_mode;  // normal 正常显示  revert 黑底白字
$g_show_mode = "normal";
#调用模式检查
if ($argc !== 2) {
    welcome();
    die();
} else {
    $page = $argv[1];
}
# 前期菜单处理
menu_process($page);
# 显示前变量初始化，调整显示大小等请进入这个函数修改
env_init();
# 全局刷新分析 
refresh();
# 显示当前页，并产生历史记录
book_render($page);

# 20170425产生全局分页记录，为跳页做准备
book_split();
# 全局处理到此结束
die();

function book_split() {
    global $g_book_var;
}

################################################################################
# 生成阅读记录，提供返回记录
################################################################################

function book_render($page) {
    global $g_book_var;
    $file_size = filesize(BOOK_FILE);
    $g_book_var['size'] = $file_size;
    $history = explode("|", $g_book_var['status']);
    $offset = 0;
    if ($page == "n") {
        $offset = $history[count($history) - 1];
    } else
    if ($page == 'p') {
        if (count($history) < 3) {
            $offset = $history[0];
        } else {
            unset($history[count($history) - 1]);
            unset($history[count($history) - 1]);
            $history = array_values($history);
            $offset = $history[count($history) - 1];
        }
    }
    $history[] = (string) getPage(intval($offset));
    $save = '';
    if (count($history) > 10) {
        for ($i = count($history) - 10; $i < count($history); $i++) {
            $save .= $history[$i] . "|";
        }
    } else {
        $save = implode("|", $history);
    }
    $g_book_var['status'] = rtrim($save, "|");
    book_var_save();
}

################################################################################
# 欢迎
################################################################################

function welcome() {
    $bg = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    $white = imagecolorallocate($bg, 255, 255, 255);
    $black = imagecolorallocate($bg, 0, 0, 0);
    imagefill($bg, 0, 0, $white);
    imagettftext($bg, 30, 0, 20, 80, $black, FONT, "inkcase i5 txt阅读器"); //
    imagettftext($bg, 20, 0, 20, 120, $black, FONT, "使用说明:"); //
    imagettftext($bg, 15, 0, 30, 160, $black, FONT, "将utf-8格式的txt文本放在ebook目录下");
    imagettftext($bg, 15, 0, 30, 190, $black, FONT, "放入inkcase连接电脑后的磁盘根目录");
    imagettftext($bg, 15, 0, 30, 220, $black, FONT, "电脑上安全卸载inkcase磁盘");
    imagettftext($bg, 15, 0, 30, 250, $black, FONT, "拔掉USB线后按住按钮直到重启");
    imagettftext($bg, 15, 0, 30, 280, $black, FONT, "再次来到这个页面就可以按键看书了");
    //resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text
    imagettftext($bg, 20, 0, 20, 330, $black, FONT, "关于:");
    imagettftext($bg, 18, 0, 30, 380, $black, FONT, "开发:");
    imagettftext($bg, 18, 0, 30, 410, $black, FONT, "     索马里的海贼(QQ:3298302054)");
    imagettftext($bg, 18, 0, 30, 440, $black, FONT, "     wuhy(QQ:10249082)");
    //   
    imagettftext($bg, 35, 0, 35, 480, $black, FONT, "按键开始阅读");
    imagettftext($bg, 15, 0, 55, 520, $black, FONT, "单击(下一页) 长按(上一页) 双击 (菜单)");
    outFunc($bg);
    imagedestroy($bg);
}

################################################################################
################################################################################

function outFunc($im) {
    if (DEBUG) {
        imagepng($im); //浏览器调试直接用gd内置函数
    } else {
        imagefile($im, "/dev/fb", 1);
    }
}

################################################################################
# 计算刷新次数择时重刷 
################################################################################

function refresh() {
    global $g_show_mode;
    if (!file_exists(REFRESH_COUNT)) {
        file_put_contents(REFRESH_COUNT, "0");
    }
    $n = intval(file_get_contents(REFRESH_COUNT));
    $n++;
    file_put_contents(REFRESH_COUNT, $n);
    if (REVERT_MODE) {
        $g_show_mode = "revert";
    }
    #  周期刷白在此更改  前一个数要比的后一个数大
    $cycle = app_config(APP, "刷新周期", 10);
    if ($cycle == 0) {
        $cycle = 4;
    }
    printf("cycle=%d\n", $cycle);
    if ($n % $cycle != ($cycle - 1) || DEBUG) {
        return;
    }
    //! 定期刷黑
    $im = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    outFunc($im);
    $g_refresh_now = true;
    if (REFRESH_MODE == "revert") {
        $g_show_mode = REVERT_MODE ? "normal" : "revert";
        return;
    }
    $g_show_mode = "normal";
    sleep(1);
    //! 再刷白
    $white = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, SCREEN_W, SCREEN_H, $white);
    outFunc($im);
    sleep(app_config(APP, "刷白停留", 0.5));
}

################################################################################
# 读取合适长度的一页并显示
################################################################################
function get_colors($bg){
    global $g_show_mode;
    //! 决定如何显示，正常还是反白
    if ($g_show_mode == "normal") {
        echo "normal mode \n";
        $white = imagecolorallocate($bg, 255, 255, 255);
        $black = imagecolorAllocate($bg, 0, 0, 0);
    } else {
        $black = imagecolorallocate($bg, 255, 255, 255);
        $white = imagecolorAllocate($bg, 0, 0, 0);
    }
    return [ $white,$black ];
}

function read_line($fp,$string){
  global $g_book_var;
  if( $string == "" ){
    $string = fgets($fp);
    if( $string === false ){
      return [false,0];
    }
    if( $g_book_var['encoding'] != 'UTF-8' ){
      $string = mb_convert_encoding($string,"UTF-8",$g_book_var['encoding']);
    }
  }
  return [$string ,strlen($string)];
}

function get_screen_line($string){
  #假设全是中文，那么读ROW个字
  $col = COL;
  $width = 0 ;
  $len = mb_strlen($string);
  for( $width=0; ($width + PADDING * 2) < SCREEN_W && $col < $len ; $col++){
    $sline = mb_substr($string,0,$col);		
    $r = imagettfbbox(FONT_SIZE,0,FONT,$sline);
    $width = $r[4]-$r[0];
  }
  $col --;
  if( $col < $len ){
    $col --;
    $sline = mb_substr($string,0,$col);
  }
  return [$sline,$col];
}


function get_screen_line_fast($string){
  static $w_config = false;
  if( $w_config === false ){
      $w_config = checkFont();
  }
  $len = mb_strlen($string);
  $t = '';
  #不是所有的字母间的pad都相同，所以这里取折中的数值
  $pad = [ 
    'enen' => -1 /*$w_config['en_pad']*/,
    'encn' => $w_config['hybid_pad'],
    'cnen' => $w_config['hybid_pad'],
    'cncn' => $w_config['cn_pad'],
  ];
  for($prev='en',$col=0,$width=0;
      ($width + PADDING * 2) < SCREEN_W && $col <= $len;
      $col++,$prev=$t){
    $ch = mb_substr($string,$col,1);
    $asc = ord($ch{0});
    $t = ($asc>127) ? "cn":"en";
    $w = ($asc>127) ? $w_config['cn_width'] : $w_config['en_width'][$asc];
    $idx = $prev.$t;
    $width += $w + $pad[$idx];
  }
  if( $col < $len ){
    $col --;
    $sline = mb_substr($string,0,$col);
  }
  return [$sline,$col];
}


function getPage($offset) {
    global $g_book_var;
    $bg = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    list($white,$black) = get_colors($bg);
    #画背景
    imagefill($bg, 0, 0, $white);
    #文本方式打开
    $fp = fopen(BOOK_FILE, "rt");
    #移动到页初
    fseek($fp, $offset);
    for($string = "" , $line = 0 ; $line < ROW ; $line ++ ){
      list($string,$len) = read_line($fp,$string);
      #文件终了
      if( $len == 0  ){
	break;
      }//if
      list( $sline , $col ) = get_screen_line_fast( $string ); 
      $text[$line]=$sline;
      #行结束，直接读偏移
      if( $col >= $len ){
	$offset = ftell($fp);
	$string = "";
      }else{
	#行中间，计算实际偏移
	if( $g_book_var['encoding'] != 'UTF-8' ){
	  $gbline = mb_convert_encoding($sline,$g_book_var['encoding'],"UTF-8");
	  $offset += strlen($gbline);
	}else{
	  $offset += strlen($sline);
	}//if utf8
	$string = mb_substr($string,$col);
      }//if 
    }//for
    fclose($fp);
    var_dump($offset);
    #画出来
    for( $i = 0 ; $i < ROW ; $i ++ ){
      imagettftext($bg, FONT_SIZE, 0, PADDING, 30 + $i* SPAN, 
      	$black, FONT, $text[$i]);
    }
    /*
     * 电池电量
     */
    if (DEBUG) {
        $fc = "86";
    } else {
        $fc = file_get_contents("/sys/class/power_supply/battery/capacity");
    }

    $txt = sprintf("%d%%", $fc);
    imagettftext($bg, 13, 0, 295, 598, $black, FONT, $txt);
    imagerectangle($bg, 330, 585, 358, 598, $black);
    $dx = 330 + (358 - 330) * intval($fc) / 100;
    imagefilledrectangle($bg, 330, 585, $dx, 598, $black);

    $rate = sprintf("%5.2f%%", $offset * 100 / $g_book_var['size']);
    imagettftext($bg, 13, 0, 10, 598, $black, FONT, $rate);

    imagettftext($bg, 13, 0, 70, 598, $black, FONT, BOOK_NAME); //显示图书名

    outFunc($bg, "/dev/fb", 1);
    imagedestroy($bg);
    return $offset;
}




function getPage2($offset) {
    global $g_book_var;
    global $g_show_mode;
    $bg = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    //! 决定如何显示，正常还是反白
    if ($g_show_mode == "normal") {
        echo "normal mode \n";
        $white = imagecolorallocate($bg, 255, 255, 255);
        $black = imagecolorAllocate($bg, 0, 0, 0);
    } else {
        $black = imagecolorallocate($bg, 255, 255, 255);
        $white = imagecolorAllocate($bg, 0, 0, 0);
    }
    imagefill($bg, 0, 0, $white);
    $fp = fopen(BOOK_FILE, "rb");
    fseek($fp, $offset);
    $string = fread($fp, 2048);
    fclose($fp);
    
    if( $g_book_var['encoding'] != 'UTF-8' ){
        echo "before iconv";
        $string = mb_convert_encoding($string,"UTF-8",$g_book_var['encoding']);
        echo "done.\n";

    }
    
    $i = 0;
    $w_config = checkFont();
    $sline = '';
    $width = 0;
    $lastword = '';
    $line = 0;
    $len = mb_strlen($string);
    
    
    while ($i < $len) {
        $word = mb_substr($string, $i, 1);
        $i++;
        $sline .= $word;
        if (ord($word) < 127) {
            $width += $w_config['en_width'][ord($word)];
            if ($lastword !== '') {
                if ($lastword == 'en') {
                    $width += $w_config['en_pad'];
                } else {
                    $width += $w_config['hybid_pad'];
                }
            } else {
                $width += $w_config['cn_pad'];
            }
            $lastword = 'en';
        } else {
            $width += $w_config['cn_width'];
            if ($lastword !== '') {
                if ($lastword == 'en') {
                    $width += $w_config['hybid_pad'];
                } else {
                    $width += $w_config['cn_pad'];
                }
            } else {
                $width += $w_config['cn_pad'];
            }
            $lastword = 'cn';
        }

        if ($word == "\n") {
            imagettftext($bg, FONT_SIZE, 0, PADDING, 30 + $line * SPAN, $black, FONT, $sline);
            $line++;
            #还原成gbk再计算
            if( $g_book_var['encoding'] != 'UTF-8' ){
                $gbline = mb_convert_encoding($sline,$g_book_var['encoding'],"UTF-8");
                $offset += strlen($gbline);
            }else{
                $offset += strlen($sline);
            }
            $sline = '';
            if (($line + 1) * SPAN + 30 > SCREEN_H) {
                break;
            }
            $width = 0;
        }
        if ($width + PADDING * 2 > SCREEN_W) {
            $sline = mb_substr($sline, 0, -1);
            $i--; //bugfix:上一行丢结尾一个字
            imagettftext($bg, FONT_SIZE, 0, PADDING, 30 + $line * SPAN, $black, FONT, $sline);
            $line++;
            if( $g_book_var['encoding'] != 'UTF-8' ){
                $gbline = mb_convert_encoding($sline,$g_book_var['encoding'],"UTF-8");
                $offset += strlen($gbline);
            }else{
                $offset += strlen($sline);
            }
            $sline = '';
            if (($line + 1) * SPAN + 30 > SCREEN_H) {
                break;
            }
            $width = 0;
        }
    }
    /*
     * 电池电量
     */
    if (DEBUG) {
        $fc = "86";
    } else {
        $fc = file_get_contents("/sys/class/power_supply/battery/capacity");
    }

    $txt = sprintf("%d%%", $fc);
    imagettftext($bg, 13, 0, 295, 598, $black, FONT, $txt);
    imagerectangle($bg, 330, 585, 358, 598, $black);
    $dx = 330 + (358 - 330) * intval($fc) / 100;
    imagefilledrectangle($bg, 330, 585, $dx, 598, $black);

    $rate = sprintf("%5.2f%%", $offset * 100 / $g_book_var['size']);
    imagettftext($bg, 13, 0, 10, 598, $black, FONT, $rate);

    imagettftext($bg, 13, 0, 70, 598, $black, FONT, BOOK_NAME); //显示图书名

    outFunc($bg, "/dev/fb", 1);
    imagedestroy($bg);
    return $offset;
}

################################################################################
# 菜单处理
################################################################################

function showMenu($offset) {
    $bg = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    $white = imagecolorallocate($bg, 255, 255, 255);
    $black = imagecolorAllocate($bg, 0, 0, 0);
    $gray = imagecolorAllocate($bg, 128, 128, 128);
    imagefill($bg, 0, 0, $white);
    $dh = opendir(APP_BASE);
    $afn = [];
    $locY = 120;
    while ($item = readdir($dh)) {
        if ($item{0} == ".") {
            continue;
        }
        if (substr(strtolower($item), -3) != "txt") {
            continue;
        }
        $afn [] = $item;
    }
    #按字节顺序对文件名排序
    sort($afn);
    $offset %= sizeof($afn);
    for ($x = 0; $x <= sizeof($afn); $x++) {
        $locY = $locY + 30;
        if ($x == $offset) {
            imagefilledrectangle($bg, 30, $locY - 10, 50, $locY, $black);
        }
        imagettftext($bg, 15, 0, 55, $locY, $black, FONT, $afn [$x]);
    }
    outFunc($bg);
    imagedestroy($bg);
}

################################################################################
# 读取指定的菜单项
################################################################################

function readMenu($menuCount) {
    $dh = opendir(APP_BASE);
    $afn = [];
    while ($item = readdir($dh)) {
        if ($item{0} == ".") {
            continue;
        }
        if (substr(strtolower($item), -3) != "txt") {
            continue;
        }
        $afn [] = $item;
    }
    sort($afn); //按字节顺序对文件名排序
    $menuCount %= sizeof($afn);
    return $afn[$menuCount];
}

################################################################################
# 各类常量的初始化
################################################################################

function env_init() {
    global $argc, $argv;
    //
    //刷新模式 sleep - 刷黑再刷白
    //        revert - 刷白再反显
    define("REFRESH_MODE", app_config(APP, "刷新模式", "revert"));
    define("REVERT_MODE", app_config(APP, '反显模式', 0) == 1);
    //
    define("FONT_SIZE", app_config(APP, '字体大小', 18));  //显示字体大小
    define("SPAN", app_config(APP, "行间距", 27));    //行间距
    define("ROW", app_config(APP, "行数", 21));    //屏幕可以容纳总行数
    define("COL", app_config(APP, "字数", 14));    //每行字数
    #默认的书籍名称
    if (!file_exists(BOOK_SELECTED)) {
        file_put_contents(BOOK_SELECTED, 'book.txt');
    }
    $current_book = file_get_contents(BOOK_SELECTED);
    #用于在界面上显示文件名
    define('BOOK_NAME', $current_book);
    $fn = APP_BASE . $current_book;
    if (!file_exists($fn)) {
        file_put_contents(BOOK_SELECTED, 'book.txt');
        $current_book = "book.txt";
    }
    define("BOOK_FILE", $fn);
    define("BOOK_VAR", sprintf("%s%s.var", APP_BASE, $current_book));
    define("REFRESH_COUNT", (DEBUG == 0) ? '/tmp/ebook_count' : 'ebook_count');
    if (DEBUG != 0) {
        define("FONT", "msyh.ttf");
        header("Content-type: image/png");
        $argc = 2;
        $argv = array('', 'n');
    }
    book_var_init();
    if (!file_exists(BOOK_FILE)) {
        echo "FILE NOT EXISTS." . BOOK_FILE;
        welcome();
        die();
    }
}

################################################################################
#保存用户进度
################################################################################

function book_var_save() {
    global $g_book_var;
    file_put_contents(BOOK_VAR, serialize($g_book_var));
}

################################################################################
#当前书的状态读取
################################################################################

function book_var_init() {
    global $g_book_var;
    if (!file_exists(BOOK_VAR)) {
        $var = ['status' => null, 'size' => filesize(BOOK_FILE), 'current' => 0];
        file_put_contents(BOOK_VAR, serialize($var));
    }
    $g_book_var = unserialize(file_get_contents(BOOK_VAR));
    if( !array_key_exists('encoding', $g_book_var) ){
        $g_book_var['encoding'] = 'unknown';
    }
    if( $g_book_var['encoding'] == 'unknown'){
      if( strtolower(substr(BOOK_FILE,0,2)) == 'gb' ){
	#对声明是gb的进行检测
        $fp = fopen(BOOK_FILE,"rb");
        $s = fread($fp,2048);
        $encoding = mb_detect_encoding($string, 'CP936,GB18030,UTF-8');
        var_dump($encoding);
        $g_book_var['encoding'] = $encoding;
      }else{
        $g_book_var['encoding'] = "UTF-8";
      }
    }
}

################################################################################
#菜单按键处理
################################################################################

function menu_process($page) {
    if ($page == "d") {//双击
        file_put_contents(MENU_COUNT, 0); //菜单的位置
        showMenu(0);
        file_put_contents(MENU_STATUS, 1); //菜单界面
        die();
    }
    //! 菜单状态文件不存在，不显示菜单
    if (!file_exists(MENU_STATUS)) {
        return;
    }
    //获得当前菜单项
    $menuCount = intval(file_get_contents(MENU_COUNT));
    if ($page == "n") {//单击,菜单下移
        $menuCount++;
        showMenu($menuCount);
        file_put_contents(MENU_COUNT, $menuCount); //保存菜单位置
        die();
    }
    if ($page == "p") {//长按,打开书
        $file = readMenu($menuCount);
        if (strtolower($file) == "off.txt") {
            show_off();
            sleep(1);
            system("/sbin/poweroff");
            die();
        }
        printf("selectd %s\n", $file);
        //保存文件名，不含路径
        save_book_selected($file);
        //删除菜单状态标志
        unlink(MENU_STATUS);
    }
}

################################################################################
# 保存当前的文件名称
################################################################################

function save_book_selected($fn) {
    file_put_contents(BOOK_SELECTED, $fn);
}

################################################################################
# 生成字体配置信息
################################################################################

function checkFont() {
    if (!file_exists(APP_BASE . basename(FONT, ".ttf") . "-" . FONT_SIZE . ".fconf")) {
        $size_cn = ImageTTFBBox(FONT_SIZE, 0, FONT, "日");
        $size_cn_w = $size_cn[4] - $size_cn[0];
        $size_cn_h = $size_cn[5] - $size_cn[1];

        $size_en = ImageTTFBBox(FONT_SIZE, 0, FONT, "a");
        $size_en_w = $size_en[4] - $size_en[0];
        $size_en_h = $size_en[5] - $size_en[1];

        $size_en = ImageTTFBBox(FONT_SIZE, 0, FONT, "aa");
        $size_en_pad = $size_en[4] - $size_en[0] - $size_en_w * 2;

        $size_cn = ImageTTFBBox(FONT_SIZE, 0, FONT, "日日");
        $size_cn_pad = $size_cn[4] - $size_cn[0] - $size_cn_w * 2;

        $size_hybid = ImageTTFBBox(FONT_SIZE, 0, FONT, "日a");
        $size_hybid_pad = $size_hybid[4] - $size_hybid[0] - $size_en_w - $size_cn_w;

        for ($i = 0; $i < 127; $i++) {
            $size_en = ImageTTFBBox(FONT_SIZE, 0, FONT, chr($i));
            $size_en_array[$i] = $size_en[4] - $size_en[0];
        }
        file_put_contents(APP_BASE . basename(FONT, ".ttf") . "-" . FONT_SIZE . ".fconf", serialize(array(
            'cn_width' => $size_cn_w,
            'cn_height' => $size_cn_h,
            'en_width' => $size_en_array,
            'cn_pad' => $size_cn_pad,
            'en_pad' => -1 /*$size_en_pad*/,
            'hybid_pad' => $size_hybid_pad
        )));
    }
    return unserialize(file_get_contents(APP_BASE . basename(FONT, ".ttf") . "-" . FONT_SIZE . ".fconf"));
}

