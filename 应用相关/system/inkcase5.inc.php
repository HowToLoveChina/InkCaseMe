<?php

mb_internal_encoding("UTF-8");

if( ! defined('DEBUG' ) ){
	define("DEBUG",false);
} //error fix
define("INKCASE_I5_DEV",true);
define("POWER_STATE","/sys/android_power/state");
define("SCREEN_W", 360);
define("SCREEN_H", 600);
define("DEFAULT_FONTFILE", "/opt/qte/fonts/msyh.ttf");


#大休眠图
define("PIC_STANDBY", "/tmp/system/resource/standby.jpg");
#小休眠图
define("PIC_ZZ", "/tmp/system/resource/zz.jpg");

#关机图
define("PIC_POWEROFF", "/tmp/system/resource/poweroff.jpg");
#小关机图
define("PIC_OFF", "/tmp/system/resource/off.jpg");


#USB连接图
define("PIC_USB", "/tmp/system/resource/usb.jpg");
#开机图,断开USB图
define("PIC_LOGO", "/mnt/udisk/logo.jpg");



#定义字模  19x13
global $Zz, $Off;
$Zz = [
    "0000000000000000000",
    "0000000000000000000",
    "0000000000000000000",
    "0001111111000000000",
    "0000000010000000000",
    "0000000100000000000",
    "0000001000011110000",
    "0000010000000100000",
    "0000100000001000000",
    "0001111111011110000",
    "0000000000000000000",
    "0000000000000000000",
    "0000000000000000000",
];
$Off = [
    "0000000000000000000",
    "0000000001000000000",
    "0000001001001000000",
    "0000100001000010000",
    "0001000001000001000",
    "0010000000000000100",
    "0100000000000000010",
    "0010000000000000100",
    "0001000000000001000",
    "0000100000000010000",
    "0000010000000100000",
    "0000000101010000000",
    "0000000000000000000"
];
$Usb = [
    "0000000000000000000",
    "0001000000000001000",
    "0001000000000001000",
    "0001000000000001000",
    "0001000000000001000",
    "0001000000000001000",
    "0001000000000001000",
    "0001000000000001000",
    "0000100000000010000",
    "0000010000000100000",
    "0000001100011000000",
    "0000000011100000000",
    "0000000000000000000"
];



if (DEBUG) {
    #如果是在本地调试可以在浏览器中输出，执行一下这里就够了

    function imagefile(resource $im, string $filename, int $fbmode) {
        imagejpeg($im);
        return;
    }

}

###############################################################
#显示睡眠图标
###############################################################

function show_zz() {
    global $Zz;

    if (file_exists(PIC_STANDBY) && system_config("使用大模式图片", 0) > 0) {
        showjpg(PIC_STANDBY, "当前为休眠模式");
        return;
    }
    if (file_exists(PIC_ZZ) && system_config("使用小模式图片", 0) > 0) {
        attachjpg(PIC_ZZ, 10, 578, 19, 13);
        return;
    }
    draw_bitmap($Zz, 10, 587);
}

###############################################################
#显示关机图标
###############################################################

function show_off() {
    global $Off;
    if (file_exists(PIC_POWEROFF) && system_config("使用大模式图片", 0) > 0) {
        showjpg(PIC_POWEROFF, "当前为关机模式");
        return;
    }
    if (file_exists(PIC_OFF) && system_config("使用小模式图片", 0) > 0) {
        attachjpg(PIC_OFF, 10, 578, 19, 13);
        return;
    }
    draw_bitmap($Off, 10, 587);
}

function show_usb() {
    global $Off;
    if (file_exists(PIC_USB) && system_config("使用大模式图片", 0) > 0) {
        showjpg(PIC_USB, "当前为USB模式");
        return;
    }
    if (file_exists(PIC_OFF) && system_config("使用小模式图片", 0) > 0) {
        attachjpg(PIC_OFF, 10, 578, 19, 13);
        return;
    }
    draw_bitmap($Off, 10, 587);
}

function show_work() {
    if (file_exists(PIC_LOGO) && system_config("使用大模式图片", 0) > 0) {
        showjpg(PIC_LOGO, "当前为工作模式");
        return;
    }
}

###############################################################
#以像素模式把图像附加上去
###############################################################

function attachjpg(string $file, int $x, int $y, int $w, int $h) {
    $im = imagecreatefromjpeg($file);
    $newim = imagecreatetruecolor($w, $h);
    if ($im === false) {
        return;
    }
    $pic_width = imagesx($im);
    $pic_height = imagesy($im);
    if (function_exists("imagecopyresampled")) {
        imagecopyresampled($newim, $im, 0, 0, 0, 0, $w, $h, $pic_width, $pic_height);
    } else {
        imagecopyresized($newim, $im, 0, 0, 0, 0, $w, $h, $pic_width, $pic_height);
    }
    imagedestroy($newim);
    $fp = fopen("/dev/fb", "wb+");
    for ($i = 0; $i < $y; $i++) {
        $s = "";
        for ($j = 0; $j < $x; $j++) {
            $co = imagecolorat($j, $i);
            $rgb565 = (($co >> 8) & 0xF800) | (($co >> 5) & 0x07E0 ) | (($co >> 3) & 0x001F );
            $s .= pack("S", $rgb565);
        }
        fseek($fp, ($y + $i) * 720 + $x * 2, 0);
        fwrite($fp, $s);
    }
    fclose($fp);
    imagedestroy($im);
}

###############################################################
#拉伸到满屏显示
###############################################################

function showjpg($file, $txt = "") {

    $im = imagecreatefromjpeg($file);
    $newim = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    if ($im === false) {
        die("open jpg error");
    }
    $pic_width = imagesx($im);
    $pic_height = imagesy($im);
    if (($pic_width > SCREEN_W) || ($pic_height > SCREEN_H)) {
        if (function_exists("imagecopyresampled")) {
            imagecopyresampled($newim, $im, 0, 0, 0, 0, SCREEN_W, SCREEN_H, $pic_width, $pic_height);
        } else {
            imagecopyresized($newim, $im, 0, 0, 0, 0, SCREEN_W, SCREEN_H, $pic_width, $pic_height);
        }
    } else {
        imagecopyresized($newim, $im, 0, 0, 0, 0, SCREEN_W, SCREEN_H, $pic_width, $pic_height);
    }
    if ($txt != "" && system_config("模式画面叠加字符", 1) > 0) {
        $co = imagecolorallocate($newim, 255, 255, 255);
        imagefilledrectangle($newim, 0, 570, SCREEN_W, SCREEN_H, $co);
        $co1 = imagecolorallocate($newim, 0, 0, 0);
        imagettftext($newim, 16, 0, 20, SCREEN_H - 10, $co1, DEFAULT_FONTFILE, $txt);
    }
    imagefile($newim, "/dev/fb", 1);
    imagedestroy($newim);
    imagedestroy($im);
}

###############################################################
#在指定的位置画图
###############################################################

function draw_bitmap($ar, $x, $y) {
    $fp = fopen("/dev/fb", "wb+");
    $lines = count($ar);
    for ($i = 0; $i < $lines; $i++) {
        fseek($fp, (($i + $y) * SCREEN_W + $x) * 2, 0);
        $str = build_line($ar[$i]);
        fwrite($fp, $str);
    }
    fclose($fp);
}

###############################################################
#生成一条线上的像素集二进制串
###############################################################

function build_line($str) {
    $i = strlen($str);
    $bin = "";
    for ($j = 0; $j < $i; $j++) {
        $ch = substr($str, $j, 1);
        if ($ch == 0) {
            $bin .= pack("S", 0x0000);
        } else
        if ($ch == 1) {
            $bin .= pack("S", 0xFFFF);
        } else {
            $bin .= pack("S", 0xEEEE);
        }
    }
    return $bin;
}

###############################################################
#把外部设备叫床
###############################################################

function dev_wakeup() {
    file_put_contents(POWER_STATE, 'wakeup');
}

###############################################################
#全体设备睡觉
###############################################################

function dev_sleep() {
    file_put_contents(POWER_STATE, 'standby');
}

###############################################################
#菜单驱动
###############################################################
/*
  检测是否需要显示菜单
 */

function menu_status(string $app) {
    $flag = sprintf("/tmp/%s.menu.show", $app);
    return file_exists($flag);
}

function menu_select(string $app, array $items, string $key) {
    $flag = sprintf("/tmp/%s.menu.show", $app);
    file_put_contents($flag, "1");
    _menu_create($app, $items);
    $fn = sprintf("/tmp/%s.menu.current", $app);
    $sel = unserialize(file_get_contents($fn));
    switch ($key) {
        case 'n':
            $sel['sel'] ++;
            file_put_contents($fn, $d = serialize($sel));
            echo $d;
            break;
        case 'd':
            $fd = sprintf("/tmp/%s.menu.json", $app);
            $items = unserialize(file_get_contents($fd));
            unlink($flag);
            unlink($fn);
            $now = $sel['sel'] % $sel['row'];
            printf("select = %d \n", $now);
            return $items[$now];
        case 'p':
            unlink($flag);
            unlink($fn);
            return "";
    }
    _menu_driver($app);
}

/*
  这是内部函数，用来生成和菜单有关的数据文件在内存中
 */

function _menu_create(string $app, array $items) {
    $file = sprintf("/tmp/%s.menu.json", $app);
    $str = serialize($items);
    if (file_exists($file)) {
        $old = file_get_contents($file);
        if ($str == $old) {
            return true;
        }
    }
    file_put_contents($file, serialize($items));
    #计算纵横
    $ar = [];
    foreach ($items as $item) {
        $ar [] = mb_strlen($item);
    }
    #最大长度
    $max = max($ar);
    #print_r($ar);
    $sel ['col'] = $max;
    $sel ['row'] = count($items);
    $sel ['sel'] = 0;
    $file2 = sprintf("/tmp/%s.menu.current", $app);
    file_put_contents($file2, serialize($sel));
    return true;
}

/*
  内部函数，这是核心菜单显示驱动
 */

function _menu_driver(string $app) {
    $FONT_HEIGHT = 35;
    #$FONT_WIDTH = 20;
    $FONT_SIZE = 20;
    $im = imagecreatetruecolor(SCREEN_W, SCREEN_H);
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, SCREEN_W, SCREEN_H, $white);
    /*
      imagerectangle($im,100,100+5,100+4*$FONT_WIDTH,100-$FONT_HEIGHT,$black);
      imagettftext($im,$FONT_SIZE,0,100,100,$black,
      DEFAULT_FONTFILE,"Tjst");
      imagefile($im,'/dev/fb',1);
      return true;
     */
    #检查是否需要显示
    $file = sprintf("/tmp/%s.menu.show", $app);
    if (!file_exists($file)) {
        return;
    }
    #取得配置信息
    $file = sprintf("/tmp/%s.menu.json", $app);
    $items = unserialize(file_get_contents($file));
    $filec = sprintf("/tmp/%s.menu.current", $app);
    $sel = unserialize(file_get_contents($filec));
    $now = $sel['sel'] % $sel['row'];
    #画外框 
    $BX = 20;
    $BY = 20;
    $BX1 = SCREEN_W - 20;
    #$BY1 = $BY + $sel['col'] * $FONT_HEIGH;
    #imagerectangle($im,$BX-1,$BY-1,$BX1 +1 ,$BY1 + 1 ,$black);
    #画内里
    for ($i = 0; $i < $sel['row']; $i++) {
        $ty = ($i + 1) * $FONT_HEIGHT + $BY;
        $tx = $BX + 1;
        $tx1 = $BX1 - 2;
        $ty1 = $ty - $FONT_HEIGHT + 2;

        if ($i == $now) {
            imagefilledrectangle($im, $tx, $ty, $tx1, $ty1, $black);
            imagettftext($im, $FONT_SIZE, 0, $tx + 4, $ty - 5, $white, DEFAULT_FONTFILE, $items[$i]);
        } else {
            imagerectangle($im, $tx, $ty, $tx1, $ty1, $black);
            imagettftext($im, $FONT_SIZE, 0, $tx + 4, $ty - 5, $black, DEFAULT_FONTFILE, $items[$i]);
        }
    }
    imagefile($im, "/dev/fb", 1);
}

/*
  外部函数，检查并切换应用
 */

function app_switch(string $app) {
    #应用目录必须存在
    $appdir = sprintf("/mnt/udisk/%s", $app);
    if (!is_dir($appdir)) {
        return false;
    }
    #应用主程序必须存在
    $appprg = sprintf("/mnt/udisk/%s/%s.php", $app, $app);
    if (!file_exists($appprg)) {
        return false;
    }
    file_put_contents("/mnt/udisk/app.txt", $app);
    return true;
}

/*
  外部函数，获得所有的想要使用的app
 */

function app_list() {
    $apps = file("/mnt/udisk/system/apps.txt");
    $ret = [];
    foreach ($apps as $app) {
        $app = trim($app);
        #应用目录必须存在
        $appdir = sprintf("/mnt/udisk/%s", $app);
        if (is_dir($appdir)) {
            $ret [] = $app;
        }
    }
    return $ret;
}

/*
  读取系统的配置
 */

function system_config(string $config, string $default = "") {
    static $cfg = false;
    if ($cfg == false) {
        foreach (file("/tmp/system/config.ini") as $line) {
            list($name, $value) = explode("=", $line);
            if ($name != "" && $value != "") {
                $cfg[$name] = trim($value);
            }//if
        }//foreach
    }
    if (array_key_exists($config, $cfg)) {
        return $cfg[$config];
    }
    return $default;
}

/*
  读取应用的配置
 */

function app_config($app, string $config, string $default = "") {
    static $cfg = [];
    if (!array_key_exists($app, $cfg)) {
        $xcfg = [];
        foreach (file(sprintf("/mnt/udisk/%s/config.ini", $app)) as $line) {
            list($name, $value) = explode("=", $line);
            if ($name != "" && $value != "") {
                $xcfg[$name] = trim($value);
            }//if
        }//foreach
        $cfg[$app] = $xcfg;
    }
    $apps = $cfg[$app];
    if (array_key_exists($config, $apps)) {
        return $apps[$config];
    }
    return $default;
}
