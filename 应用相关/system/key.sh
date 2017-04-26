#!/bin/sh

#取得键码
key=$1
#将当前时间写入内存文件
date +%s > /tmp/keystamp

#没有指定APP时，按键不理，也可能是因为文件系统被挂载了
if [ ! -e /mnt/udisk/app.txt ]; then
  exit;
fi 

#取得当前的应用
app=`cat /mnt/udisk/app.txt`

#充电模式下按键就进入应用模式
if [ ! -e /mnt/udisk/${app}/${app}.php ]; then
  umount_usb
fi 


#根据不同的应用分发给程序
if [ x$1 == x28 ]; then
	#单击
	/opt/bin/php /mnt/udisk/${app}/${app}.php n
	exit
fi

if [ x$1 == x63 ]; then
	#长按
	/opt/bin/php /mnt/udisk/${app}/${app}.php p
	exit
fi

if [ x$1 == x33 ]; then
	/opt/bin/php /mnt/udisk/${app}/${app}.php e
	exit
fi

if [ x$1 == x61 ]; then
	#唤醒以后获得这个
	/opt/bin/php /mnt/udisk/${app}/${app}.php n
	exit
fi

if [ x$1 == x66 ]; then
	#双击
	/opt/bin/php /mnt/udisk/${app}/${app}.php d
	exit
fi

