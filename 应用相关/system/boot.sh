#!/bin/sh
#我是主启动脚本


app=`cat /mnt/udisk/app.txt`
boot=/mnt/udisk/${app}/boot.sh

#将脚本复制到/tmp防止分区无法卸载问题
cp -R /mnt/udisk/system /tmp
#启动定时休眠的检测脚本
/opt/bin/php /tmp/system/sleep.php & 

#根据需要显示启动图
if [ -e /mnt/udisk/logo.jpg ];then
   /opt/bin/php /tmp/system/showjpg.php /mnt/udisk/logo.jpg
fi

#生成缓存文件
dd if=/dev/zero of=/tmp/ufb bs=720 count=600



#将可执行程序复制到/tmp下，防止无法卸载问题
chmod +x /tmp/system/button

if [ -e ${boot} ];then
	#把应用复制到内存里，然后启动
	cp -R /mnt/udisk/${app} /tmp
	sh /tmp/${app}/boot.sh
fi

#后台执行，防止boot.sh老在内存里
/tmp/system/button  & 


