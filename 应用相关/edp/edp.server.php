<?php

define("EDP_TYPE_FILL"  ,1);
define("EDP_TYPE_PIXEL" ,2);
define("EDP_TYPE_COMMIT",3);
define("EDP_TYPE_BLOCK_TEXT"  ,11);
define("EDP_TYPE_BLOCK_DETEXT",12);
define("EDP_TYPE_FLOAT_TEXT"  ,13);
define("EDP_TYPE_FLOAT_DETEXT",14);


/*
    81180100000000EEEE
81180B000A00050001
30
811803000000000000
    
*/


global $im;

$im = imagecreatetruecolor(360,600);
imagefile($im,"/dev/fb",1);

/*
  8118 01 0023 0034 0005 BASE64\r\n  写HELLO
  8118,01,0002,0003 FFFF            画个点
*/

define("EDP_DATA_SOURCE","stdin");


#电子显示器

#打开一个管道
$ds = edp_open();

while( $pkt = edp_read_header($ds) ){
  if( sizeof($pkt) != 4 ){
    continue;
  }
  if( $pkt['t'] >= 10 ){
    $pkt['d'] = edp_read_payload( $pkt['len'] );
  }
  
define("EDP_TYPE_BLOCK_TEXT"  ,11);
define("EDP_TYPE_BLOCK_DETEXT",12);
define("EDP_TYPE_FLOAT_TEXT"  ,13);
define("EDP_TYPE_FLOAT_DETEXT",14);
  
  switch($pkt['t']){
    case EDP_TYPE_COMMIT:
      imagefile($im,'/dev/fb',1);
      break;
    case EDP_TYPE_FILL:
      edp_cmd_fill( $pkt );
      break;
    case EDP_TYPE_PIXEL:
      edp_cmd_pixel( $pkt );
      break;
    case EDP_TYPE_BLOCK_TEXT:
      edp_cmd_block_text( $pkt );
      break;
    case EDP_TYPE_BLOCK_DETEXT:
      edp_cmd_block_detext( $pkt );
      break;
    case EDP_TYPE_FLOAT_TEXT:
      edp_cmd_float_text( $pkt );
      break;
    case EDP_TYPE_FLOAT_DETEXT:
      edp_cmd_float_detext( $pkt );
      break;
  }
}



function edp_cmd_fill($pkt){
  global $im;
  $ci = $pkt['len'] & 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagefilledrectangle($im,0,0,360,600,$co);
}

function edp_cmd_pixel($pkt){
  global $im;
  $ci = $pkt['len'] & 0xff;
  $co = imagesetpixel($im,$pkt['x'],$pkt['y'],$ci);
}
//反色的字
function edp_cmd_block_detext($pkt){
  global $im;
  define("FONT_SIZE",18);
  $str = $pkt['d'];
  $pos = imagettfbbox($im,FONT_SIZE,0,FONT_FILE,$str);
  $dx = $pos[2] - $pos[0];
  $dy = $pos[7] - $pos[1];
  $ldx = $pkt['x'] * CH_WIDTH ;
  $ldy = $pkt['y'] * CH_HEIGHT;
  $ci = 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagefilledrectangle($im,$ldx,$ldy,$dx,$dy,$co);
  $ci = $pkt['len'] & 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagettftext($im,FONT_SIZE,0,$ldx,$ldy, $co , FONT_FILE , $str );
/*
0 	左下角 X 位置       (6,7)                     (4,5)
1 	左下角 Y 位置         +------------------------+
2 	右下角 X 位置         |                        |
3 	右下角 Y 位置         |                        |
4 	右上角 X 位置         |                        |
5 	右上角 Y 位置         +------------------------+
6 	左上角 X 位置       (0,1)                     (2,3)
7 	左上角 Y 位置
*/
}


function edp_cmd_block_text($pkt){
  global $im;
  define("FONT_SIZE",18);
  $str = $pkt['d'];
  $pos = imagettfbbox($im,FONT_SIZE,0,FONT_FILE,$str);
  $dx = $pos[2] - $pos[0];
  $dy = $pos[7] - $pos[1];
  $ldx = $pkt['x'] * CH_WIDTH ;
  $ldy = $pkt['y'] * CH_HEIGHT;
  $ci = 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagefilledrectangle($im,$ldx,$ldy,$dx,$dy,$co);
  $ci = $pkt['len'] & 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagettftext($im,FONT_SIZE,0,$ldx,$ldy, $co , FONT_FILE , $str );
/*
0 	左下角 X 位置       (6,7)                     (4,5)
1 	左下角 Y 位置         +------------------------+
2 	右下角 X 位置         |                        |
3 	右下角 Y 位置         |                        |
4 	右上角 X 位置         |                        |
5 	右上角 Y 位置         +------------------------+
6 	左上角 X 位置       (0,1)                     (2,3)
7 	左上角 Y 位置
*/
}
//反色的字
function edp_cmd_float_detext($pkt){
  global $im;
  define("FONT_SIZE",18);
  $str = $pkt['d'];
  $pos = imagettfbbox($im,FONT_SIZE,0,FONT_FILE,$str);
  $dx = $pos[2] - $pos[0];
  $dy = $pos[7] - $pos[1];
  $ldx = $pkt['x'] * CH_WIDTH ;
  $ldy = $pkt['y'] * CH_HEIGHT;
  $ci = 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagefilledrectangle($im,$ldx,$ldy,$dx,$dy,$co);
  $ci = $pkt['len'] & 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagettftext($im,FONT_SIZE,0,$ldx,$ldy, $co , FONT_FILE , $str );
}


function edp_cmd_float_text($pkt){
  global $im;
  define("FONT_SIZE",18);
  $str = $pkt['d'];
  $pos = imagettfbbox($im,FONT_SIZE,0,FONT_FILE,$str);
  $dx = $pos[2] - $pos[0];
  $dy = $pos[7] - $pos[1];
  $ldx = $pkt['x'] * CH_WIDTH ;
  $ldy = $pkt['y'] * CH_HEIGHT;
  $ci = 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagefilledrectangle($im,$ldx,$ldy,$dx,$dy,$co);
  $ci = $pkt['len'] & 0xff;
  $co = imagecolorallocate($im,$ci,$ci,$ci);
  imagettftext($im,FONT_SIZE,0,$ldx,$ldy, $co , FONT_FILE , $str );
/*
0 	左下角 X 位置       (6,7)                     (4,5)
1 	左下角 Y 位置         +------------------------+
2 	右下角 X 位置         |                        |
3 	右下角 Y 位置         |                        |
4 	右上角 X 位置         |                        |
5 	右上角 Y 位置         +------------------------+
6 	左上角 X 位置       (0,1)                     (2,3)
7 	左上角 Y 位置
*/
}



function edp_open(){
  if( EDP_DATA_SOURCE == "stdin" ){
    return STDIN;
  }
  if( EPD_DATA_SOURCE == "pipe" ){
    $pipe = new Pipe("edp");
    $pipe->open_read();
    return $pipe;
  }
  return false;
}

function edp_read_header($ds){
  if( EDP_DATA_SOURCE == "stdin" ){
    return edp_read_header_stdin($ds);
  }
  if( EDP_DATA_SOURCE == "pipe" ){
    return edp_read_header_pipe($ds);
  }
  return false;
}

function edp_read_header_stdin($ds){
  $str = fgets($ds);
  if( $str === false ){
    return false;
  }
  if( substr($str,0,4) != '8118' ){
    return true;
  }
  $t = substr($packet,4,2);
  $x = substr($packet,6 ,4);
  $y = substr($packet,10 ,4);
  $s = substr($packet,14,4);
  return  [ 't' => $t , 'x' => hex2bin($x) , 'y' => hex2bin($y) , 'len' => hex2bin($s) ];
}
function edp_read_header_pipe($ds){
  $magic = { '8','1','1','8');
  for( $i = 0 ; $i < sizeof($magic);$i++){
    $ch = $pipe->read(1);
    if( $ch === false ){
      return false;
    }
    if( $ch != $magic[$i] ){
      return true;
    }
  }
  $packet = $pipe->read(14);
  if( strlen($packet)!=14 ){
    return true;
  }
  $t = substr($packet,0 ,2);
  $x = substr($packet,2 ,4);
  $y = substr($packet,6 ,4);
  $s = substr($packet,10,4);
  return  [ 't' => $t , 'x' => hex2bin($x) , 'y' => hex2bin($y) , 'len' => hex2bin($s) ];
}
