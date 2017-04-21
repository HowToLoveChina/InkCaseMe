<?php

#引入功能库
include_once(dirname(__FILE__)."/inkcase5.inc.php");


if( $argv[1] == "" ){
	$file = "/mnt/udisk/logo.jpg";
}else{
	$file = $argv[1];
}

$txt = $argv[2];

showjpg($file);


