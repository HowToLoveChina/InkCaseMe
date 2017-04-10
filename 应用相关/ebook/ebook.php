<?php
define("APP_BASE",dirname(__FILE__) . "/" );
include(APP_BASE."/../system/inkcase5.inc.php");
/*
  20170407 加入定期全刷，减少越来越黑的问题
  20170407 读取电池电量显示在右下角
  20170407 加入standby处理，减少功耗
  20170408 待机功能交给sleep脚本处理，不在这里处理
*/

/*
* 20170407 
* 更新换书检查逻辑 增加欢迎页面 另加了一个debug逻辑 方便浏览器调试 
* 减小文字，增大页面显示容量
* 修改换书逻辑 
* bugfix：
* 半角字符导致页面显示不整齐
*/

define("FONT_SIZE",18);		//显示字体大小
define("SPAN", 27); 		//行间距
define("ROW", 21); 			//屏幕可以容纳总行数
define("COL", 14); 			//每行字数

define("BOOK_FILE", APP_BASE . "book.txt");
define("BOOK_STATUS", APP_BASE . "bookstatus");
define("BOOK_SIZE", APP_BASE . "booksizet");

if(DEBUG == 0){
	define("FONT","/opt/qte/fonts/msyh.ttf");
	define("REFRESH_COUNT",'/tmp/ebook_count');
}else{
	header ("Content-type: image/bmp");
	define("REFRESH_COUNT",'ebook_count');
	$argc = 2;
	$argv = array('','n');
}

//
if ($argc !== 2) {
    welcome();
    die();
}
if (!file_exists(BOOK_FILE)) {
    welcome();
    die();
}
$page   = $argv[1];
$Offset = 0;

//定期全刷 
refresh();


/*
 *阅读记录，提供返回记录
 */

$file_size = filesize(BOOK_FILE);
if (!file_exists(BOOK_SIZE)) {
    file_put_contents(BOOK_SIZE, $file_size);
} else {
    if (file_get_contents(BOOK_SIZE) != $file_size) {
        file_put_contents(BOOK_SIZE, $file_size);
        file_put_contents(BOOK_STATUS, "0");
    }
}
if (!file_exists(BOOK_STATUS)) {
    file_put_contents(BOOK_STATUS, "0");
    $Offset = 0;
} else {
    $sfile   = file_get_contents(BOOK_STATUS);
    $history = explode("|", $sfile);
    
    if ($page == "n") {
        $offset = $history[count($history) - 1];
    } else {
        if (count($history) < 3) {
            $offset = $history[0];
        } else {
            unset($history[count($history) - 1]);
            unset($history[count($history) - 1]);
            $history = array_values($history);
            $offset  = $history[count($history) - 1];
        }
    }
}
$history[] = (string) getPage($offset);
$save      = '';
if (count($history) > 10) {
    for ($i = count($history) - 10; $i < count($history); $i++) {
        $save .= $history[$i] . "|";
    }
    
} else {
    $save = implode("|", $history);
}
file_put_contents(BOOK_STATUS, rtrim($save, "|"));

#进入全局休眠
/*
* 计算刷新次数择时重刷 
*/

function welcome() {
    $bg    = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    $white = imagecolorallocate($bg, 255, 255, 255);
    $black = imagecolorAllocate($bg, 0, 0, 0);
    imagefill($bg, 0, 0, $white);
    imagettftext($bg, 30, 0, 20, 80, $black, FONT, "inkcase i5 txt阅读器");
    imagettftext($bg, 20, 0, 20, 120, $black, FONT, "使用说明:");
    imagettftext($bg, 15, 0, 30, 160, $black, FONT, "将utf-8格式的txt文本改名为 book.txt");
    imagettftext($bg, 15, 0, 30, 190, $black, FONT, "放入inkcase连接电脑后的磁盘根目录");
    imagettftext($bg, 15, 0, 30, 220, $black, FONT, "电脑上安全卸载inkcase磁盘");
    imagettftext($bg, 15, 0, 30, 250, $black, FONT, "拔掉USB线后按住按钮直到重启");
    imagettftext($bg, 15, 0, 30, 280, $black, FONT, "再次来到这个页面就可以按键看书了");
    
    imagettftext($bg, 20, 0, 20, 330, $black, FONT, "关于:");
    imagettftext($bg, 18, 0, 30, 380, $black, FONT, "开发:索马里的海贼(QQ:3298302054)");
    
    imagettftext($bg, 35, 0, 35, 480, $black, FONT, "按键开始阅读");
    imagettftext($bg, 15, 0, 55, 520, $black, FONT, "单击(下一页) 双击(上一页)");
    outFunc($bg);
    imagedestroy($bg);
}

function outFunc($im){
	if(DEBUG){
		imagebwbmp($im);
	}else{
		imagefile($im,"/dev/fb",1);
	}
}
function SBC_DBC($str) {
    $DBC = Array(
        '０' , '１' , '２' , '３' , '４' ,
        '５' , '６' , '７' , '８' , '９' ,
        'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
        'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
        'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
        'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
        'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
        'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
        'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
        'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
        'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
        'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
        'ｙ' , 'ｚ' , '－' , '　' , '：' ,
        '．' , '，' , '／' , '％' , '＃' ,
        '！' , '＠' , '＆' , '（' , '）' ,
        '＜' , '＞' , '＂' , '＇' , '？' ,
        '［' , '］' , '｛' , '｝' , '＼' ,
        '｜' , '＋' , '＝' , '＿' , '＾' ,
        '￥' , '￣' , '｀'
    );
    $SBC = Array(
        '0', '1', '2', '3', '4',
        '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E',
        'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T',
        'U', 'V', 'W', 'X', 'Y',
        'Z', 'a', 'b', 'c', 'd',
        'e', 'f', 'g', 'h', 'i',
        'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x',
        'y', 'z', '-', ' ', ':',
        '.', ',', '/', '%', '#',
        '!', '@', '&', '(', ')',
        '<', '>', '"', '\'','?',
        '[', ']', '{', '}', '\\',
        '|', '+', '=', '_', '^',
        '$', '~', '`'
    );
	return str_replace($SBC, $DBC, $str);
}

function refresh(){
  if (!file_exists(REFRESH_COUNT)) {
    file_put_contents(REFRESH_COUNT,"0");
  }
  $n = intval(file_get_contents(REFRESH_COUNT));
  $n++;
  file_put_contents(REFRESH_COUNT,$n);
  if( $n%5!=4 || DEBUG){
    return;
  }
  //! 定期刷白
  $im = imagecreatetruecolor(SCREEN_W,SCREEN_H);
  outFunc($im);
  sleep(1);
  $white = imagecolorallocate($im, 255, 255, 255);
  imagefilledrectangle($im,0,0,SCREEN_W,SCREEN_H,$white);
  outFunc($im);
  sleep(0.5);
}

/*
 *读取合适长度的一页并显示
 */
function getPage($offset) {
    $file  = BOOK_FILE;
    $fsize = filesize($file);
    $bg    = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    $white = imagecolorallocate($bg, 255, 255, 255);
    $black = imagecolorAllocate($bg, 0, 0, 0);
    imagefill($bg, 0, 0, $white);
    $fp = fopen($file, "rb");
    fseek($fp, $offset);
    $string = fread($fp, 2048);
	$string = str_replace("    ","  ",$string); //有些小说开头4个空格 转换为全角的话4个字符的空格比较难看，所以换成2个空格
    fclose($fp);
    $content  = '';
    $i        = 0;
    $line     = 0;
    $autowrap = 0;
    while ($line < ROW) {
        $pos = mb_strpos(mb_substr($string, $i, COL), "\n");
        if ($pos !== false) {
            $content .= $sline = mb_substr($string, $i, $pos + 1);
            $i += $pos + 1;
        } else {
            //$content .= mb_substr($string, $i, COL) . "\n";
            $content .= $sline = mb_substr($string, $i, COL) . "\n"; 
            $i += COL;
            $autowrap++;
        }
        //! 分行显示加点间距
        $sline = SBC_DBC($sline);
        imagettftext($bg, FONT_SIZE, 0, 8, 30 + $line * SPAN, $black, FONT, $sline);
        //! 
        $line++;
    }
	$nnnn_count = mb_substr_count($content,"  "); 
    $offset += strlen($content) - $autowrap + $nnnn_count*2; //替换4个空格为2个之后 要重新计算offset
    #$content = SBC_DBC($content); //半角字符宽度导致显示不齐，现全转为全角。
	#imagettftext($bg, FONT_SIZE, 0, 8, 30, $black, FONT, $content);
    /*
     *电池电量
     */
	 if(DEBUG){
		 $fc = "86";
	 }else{
		 $fc = file_get_contents("/sys/class/power_supply/battery/capacity");
	 }
    
    $txt = sprintf("%d%%",$fc);
    imagettftext($bg, 13, 0, 295, 598, $black, FONT, $txt);
    imagerectangle($bg, 330, 585, 358, 598, $black);
    $dx = 330 + (358-330)*intval($fc)/100 ;
    imagefilledrectangle($bg, 330, 585, $dx, 598, $black);
    
    $rate = sprintf("%5.2f%%",$offset*100/$fsize); 
    imagettftext($bg, 13, 0, 10, 598, $black, FONT, $rate);
    
    outFunc($bg, "/dev/fb", 1);
    imagedestroy($bg);
    return $offset;
    
}
function imagebwbmp($image, $to = null, $threshold = 0.5)
{
    if (func_num_args() < 1) {
        $fmt = "imagebwbmp() expects a least 1 parameters, %d given";
        trigger_error(sprintf($fmt, func_num_args()), E_USER_WARNING);
        return;
    }
    if (!is_resource($image)) {
        $fmt = "imagebwbmp() expects parameter 1 to be resource, %s given";
        trigger_error(sprintf($fmt, gettype($image)), E_USER_WARNING);
        return;
    }
    if (!is_numeric($threshold)) {
        $fmt = "imagebwbmp() expects parameter 3 to be float, %s given";
        trigger_error(sprintf($fmt, gettype($threshold)), E_USER_WARNING);
        return;
    }

    if (get_resource_type($image) !== 'gd') {
        $msg = "imagebwbmp(): supplied resource is not a valid gd resource";
        trigger_error($msg, E_USER_WARNING);
        return false;
    }
    switch (true) {
        case $to === null:
            break;
        case is_resource($to) && get_resource_type($to) === 'stream':
        case is_string($to) && $to = fopen($to, 'wb'):
            if (preg_match('/[waxc+]/', stream_get_meta_data($to)['mode'])) {
                break;
            }
        default:
            $msg = "imagebwbmp(): Invalid 2nd parameter, it must a writable filename or a writable stream";
            trigger_error($msg, E_USER_WARNING);
            return false;
    }

    if ($to === null) {
        $to = fopen('php://output', 'wb');
    }

    $biWidth = imagesx($image);
    $biHeight = imagesy($image);
    $biSizeImage = ((int)ceil($biWidth / 32) * 32 / 8 * $biHeight);
    $bfOffBits = 54 + 4 * 2; // Use two colors (black and white)
    $bfSize = $bfOffBits + $biSizeImage;
    
    fwrite($to, 'BM');
    fwrite($to, pack('VvvV', $bfSize, 0, 0, $bfOffBits));
    fwrite($to, pack('VVVvvVVVVVV', 40, $biWidth, $biHeight, 1, 1, 0, $biSizeImage, 0, 0, 0, 0));
    fwrite($to, "\xff\xff\xff\x00"); // white
    fwrite($to, "\x00\x00\x00\x00"); // black
    
    for ($y = $biHeight - 1; $y >= 0; --$y) {
        $byte = 0;
        for ($x = 0; $x < $biWidth; ++$x) {
            $rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            $value = (0.299 * $rgb['red'] + 0.587 * $rgb['green'] + 0.114 * $rgb['blue']) / 0xff;
            $color = (int)($value > $threshold);
            $byte = ($byte << 1) | $color;
            if ($x % 8 === 7) {
                fwrite($to, pack('C', $byte));
                $byte = 0;
            }
        }
        if ($x % 8) {
            fwrite($to, pack('C', $byte << (8 - $x % 8)));
        }
        if ($x % 32) {
            fwrite($to, str_repeat("\x00", (int)((32 - $x % 32) / 8)));
        }
    }

    return true;
}



?>
