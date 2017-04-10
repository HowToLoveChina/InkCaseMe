<?php


$dev = "/dev/ttyACM0";
$output = fopen($dev,"w+");

clear_input($output);
edp_start($output);
clear_input($output);
edp_fill($output,0x55);
clear_input($output);
edp_commit($output);
clear_input($output);
sleep(3);
edp_float_text($output,10,5,"我是中国人");
clear_input($output);
edp_commit($output);
clear_input($output);
edp_stop($output);
clear_input($output);

/*
81180B000A0005001E
e68891e698afe4b8ade59bbde4baba
811803000000000000
*/

function clear_input($fp){
  while( readable($fp,500) ){
    echo fgets($fp);
  }
}

function readable($fp, int $timeout=100){
  $r = [$fp] ;
  $w = [$fp] ; 
  $e = [$fp] ;
  return  stream_select( $r, $w , $e , $timeout ) > 1;
}

function edp_start($stream){
  #fwrite($stream,"root \n");
  fwrite($stream," date >> /tmp/edp.log ; php /mnt/udisk/edp/edp.php  \n");
  sleep(1);
}

function edp_stop($stream){
  fwrite($stream,"killall php \n");
}


function edp_commit($stream){
  fwrite($stream,"81180300000000FFFF\r\n");
}

function edp_fill($stream,$color){
  fprintf($stream,"81180100000000%04X\r\n",$color&0xFFFF);
}

function edp_float_text($stream,int $x,int $y,string $content){
  $payload = bin2hex($content);
  $str = sprintf("8118%02X%04X%04X%04X\r\n",11,$x,$y,strlen($payload));
  fwrite($stream,$str);
  echo $str;
  $str = sprintf("%s\r\n",$payload);
  fwrite($stream,$str);
  echo $str;
  
}