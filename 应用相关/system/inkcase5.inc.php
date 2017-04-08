<?php
mb_internal_encoding("UTF-8");


define("DEBUG",false);
define("INKCASE_I5_DEV",true);
define("POWER_STATE","/sys/android_power/state");
define("SCREEN_W", 360);
define("SCREEN_H", 600);


if( DEBUG ){
  #如果是在本地调试，执行一下这里就够了
  function imagefile(resource $im , string $filename , int  $fbmode ){
    return ;
  }
}


###############################################################
#在指定的位置画图
###############################################################
function draw_bitmap($ar,$x,$y){
  $fp =fopen("/dev/fb","wb+");  
  $lines = count($ar);          
  for($i = 0 ; $i < $lines ; $i++){
    fseek( $fp , (($i+$y)*360+$x)*2 , 0 );
    $str = build_line($ar[$i]);           
    fwrite($fp,$str);                     
  }                                       
  fclose($fp);                            
}                                         
###############################################################
#生成一条线上的像素集二进制串
###############################################################
function build_line($str){                
  $i = strlen($str);                      
  $bin = "";                              
  for($j=0;$j<$i;$j++){                   
    $ch = substr($str,$j,1);              
    if( $ch == 0 ){                       
        $bin .= pack("S",0x0000);         
    }else                                 
    if( $ch == 1 ){                       
        $bin .= pack("S",0xFFFF);
    }else{                       
        $bin .= pack("S",0xEEEE);
    }                                     
  }                              
  return $bin;                   
}            
###############################################################
#把外部设备叫床
###############################################################
function dev_wakeup(){
  file_put_contents(POWER_STATE,'wakeup');
}
###############################################################
#全体设备睡觉
###############################################################
function dev_sleep(){
  file_put_contents(POWER_STATE,'standby');
}

