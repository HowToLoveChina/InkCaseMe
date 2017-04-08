<?php

#引入功能库
include_once(dirname(__FILE__)."/inkcase5.inc.php");

#定义字模
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
#计算未按键时间多于30秒就睡觉，如果连接着USB，会睡不成，所以不再单独处理
file_put_contents('/tmp/keystamp',time());
while(true){
	$delta = time()-file_get_contents("/tmp/keystamp");
	if( $delta > 30 ){
		file_put_contents('/tmp/keystamp',time());
		draw_bitmap($Zz,10,587);
		#让设备有时间画出来
		sleep(1);
		dev_sleep
		file_put_contents("/sys/android_power/state","standby");
		sleep(5);
	}else{
		sleep(5);
	}	
}	




















