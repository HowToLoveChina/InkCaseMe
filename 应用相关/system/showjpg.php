<?php

if( $argv[1] == "" ){
	$file = "/mnt/udisk/logo.jpg";
}else{
	$file = $argv[1];
}
showjpg($file);


#拉伸到满屏显示
function showjpg($file){
    $maxwidth=360;
    $maxheight=600;

    $im = imagecreatefromjpeg($file);
    $newim = imagecreatetruecolor(360,600);
    if( $im === false ){
    	die("open jpg error");
    }
    $pic_width = imagesx($im);
    $pic_height = imagesy($im);
    if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)){
        if(function_exists("imagecopyresampled")){
           imagecopyresampled($newim,$im,0,0,0,0,$maxwidth,$maxheight,$pic_width,$pic_height);
        }else{
           imagecopyresized($newim,$im,0,0,0,0,$maxwidth,$maxheight,$pic_width,$pic_height);
        }
        imagefile($newim,"/dev/fb",1);
    }else{
        imagecopyresized($newim,$im,0,0,0,0,$pic_width,$pic_height,$pic_width,$pic_height);
        imagefile($newim,"/dev/fb",1);
    }           
    imagedestroy($newim);
    imagedestroy($im);
}



