<?php
include_once("/mnt/udisk/system/inkcase5.inc.php");


#菜单驱动测试

#定义菜单
$items = [ '化学入门','Abcdef.&((**',"超凡入圣仙经"];
#定义自己的名称
$app = 'menu';

#获得按键值
$key = $argv[1];

if( menu_status($app) ){
    $item = menu($app,$items,$key);
    if( $item === false ){
      //长按，取消菜单
    }else
    if( $item == "" ){
      //还在显示处理中
      return;
    }else{
      //$item是选择项的结果信息
    }
}

switch($key){
  case 'n': //单击
  case 'p': //长按
    #触发菜单显示
    menu($app,$items);
    break;
  case 'd': //双击
}



