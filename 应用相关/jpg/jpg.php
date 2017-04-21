<?php
#############################################################################
# 功能：图片浏览器
# 使用：
#     1.将根目录的app.txt内容写为jpg
#     2.在本目录里复制任意数量，任意大小的jpg图片
#     3.按一次后翻一张，长按为前翻
# 修改：
#     20170408 基于代码库减少代码量
#############################################################################

define("APP_BASE",dirname(__FILE__) . "/" );
include(APP_BASE."/../system/inkcase5.inc.php");

$page   = isset($argv[1]) ? $argv[1] : "n";
$Offset = 0;

if ($page == "d") {//双击
  show_off();
  sleep(1);
  system("/sbin/poweroff");
  die();
}


$dh = opendir(APP_BASE);
$afn=[];
while($item = readdir($dh) ){
  if( $item{0} == "."){
    continue;
  }
  $r = pathinfo(strtolower($item));
  if( $r['extension'] != "jpg" ){
  	continue;
  }
  $afn [] = $item;
}
#按字节顺序对文件名排序
sort($afn);

$jpg = APP_BASE . get_next_file( $afn );
echo $jpg;
showjpg( $jpg );

function get_next_file(array $afn ){
  define("IDX_FILE","/tmp/jpg.index");
  if( ! file_exists( IDX_FILE ) ){
    file_put_contents( IDX_FILE , "-1");	
  }
  $fn = intval(file_get_contents(IDX_FILE)) ;
  $fn++;
  $fn %= sizeof($afn);
  file_put_contents( IDX_FILE , $fn );
  return $afn[$fn];
}
$jpg = $afn[$fn];


