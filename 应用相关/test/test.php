<?php

/*
  这是一个演示性的项目 ，指导用户完成一个基本的应用
*/


#


#这是应用程序的名字，!!必须!!和你的文件名一致，和所在目录一致
define("APP","test");

#这句不用改，只是为了调用资源时提供便利
define("APP_BASE",dirname(__FILE__) . "/" );


#这里引入了功能库，以后可能会不断增加
include(APP_BASE."/../system/inkcase5.inc.php");




$key = $argv[1];



switch($key){
  case 'n':  #下一页,默认是指单击
    go_next();
    break;
  case 'p':  #上一页，默认是指长按
    go_prev();
    break;
  case 'd':  #出菜单，默认是指双击
    go_menu();
    break;
}
die();

function go_next(){
}

function go_prev(){
}

function go_menu(){
}

function is_menu(){
  return file_exists( sprintf("/tmp/%s.menu",APP) );
}



var_dump( app_config("配置项1",4));
var_dump( app_config("配置项2","good"));
var_dump( app_config("配置项3","IS ME "));
