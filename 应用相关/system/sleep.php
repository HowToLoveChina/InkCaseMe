<?php

#引入功能库
include_once(dirname(__FILE__)."/inkcase5.inc.php");



# 计算未按键时间多于30秒就睡觉，如果连接着USB，会睡不成，所以不再单独处理
file_put_contents('/tmp/keystamp',time());
define("VBUS_STATUS","/sys/bus/platform/drivers/dwc_otg/vbus_status");
define("USB_ONLINE","/sys/devices/platform/rockchip_battery/power_supply/usb/online");


# 获得原始状态，相应的功能在启动脚本中已经处理
$last_usb = get_vbus_status() || get_usb_online() ;
# 把USB当成串口用的应用时，不挂载
$app = file_get_contents("/mnt/udisk/app.txt");
# 取得app是否使用串口
$app_flag = file_exists(sprintf("/mnt/udisk/%s/usbserial.txt",$app));

$last_vbus = get_vbus_status();
$last_online = get_usb_online();
$ufb = file_exists("/mnt/udisk/filefb");


while(true){
	#启用了filefb功能，并且USB存储上线了，那么每秒刷新屏幕
	if( $ufb &&  $online ){
	  system("dd if=/tmp/ufb of=/dev/fb bs=432000 2>/dev/null");
	}
	#睡一秒，让机器反应一下
	sleep(1);
	$delta = time()-file_get_contents("/tmp/keystamp");
	$vbus = get_vbus_status();
	$online = get_usb_online();
	$usbtty = get_usb_tty();
	#作为串口使用时，不处理
	if( $usbtty || $app_flag ){
	  continue;
	}
	if( $delta > 30 && ! $vbus && ! $online ){
		//没有在充电，没有作为U盘被挂载
		show_zz();
		#让设备有时间画出来
		sleep(1);
		file_put_contents("/sys/android_power/state","standby");
		//! 防止立即睡着
		update_stamp();
		#唤醒以后立即刷新一次应用,改成在key里接受按键事件来处理
		continue;
	}//if delta
	#没变化
	if( $last_vbus == $vbus  && $online == $last_online ){
	  continue;
	}

	if( ($vbus && ! $last_vbus) && ! $online  ){
	  #上电了
	  mount_usb();
	}else
	if( $last_vbus == true && $vbus == false ){
	  umount_usb();
	}else
	if( ! $online  && $last_online ){
	  umount_usb();
	}
	$last_vbus = $vbus ;
	$last_online = $online;
}//while


function mount_usb(){
	  //必须先上图，否则就没文件了
	  show_usb();
	  //上电了,卸载U盘，挂载服务
	  system("/bin/umount /mnt/udisk");
	  system("/sbin/insmod /lib/g_file_storage.ko file=/dev/mtdblock5 stall=0 removable=1");
	  //! 防止立即睡着
	  update_stamp();
}

function umount_usb(){
	  //掉电了，挂载U盘
	  system("rmmod g_file_storage.ko");
	  system("umount /mnt/udisk");
	  system("mount -t vfat -o iocharset=utf8 /dev/mtdblock5 /mnt/udisk");
	  //只能后刷图，否则也看不到
	  show_work();
	  //! 防止立即睡着
	  update_stamp();
}

#取得是否有电
function get_vbus_status(){
  return intval(file_get_contents(VBUS_STATUS)) == 1;
}
function get_usb_online(){
  return intval(file_get_contents(USB_ONLINE)) == 1 ;
}
function get_usb_tty(){
  return file_exists("/mnt/udisk/usbtty" );
}

#更新刷新时间
function update_stamp(){
  file_put_contents('/tmp/keystamp',time());
}