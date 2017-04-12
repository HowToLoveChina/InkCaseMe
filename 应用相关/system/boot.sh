#!/bin/sh
#我是主启动脚本

#将脚本复制到/tmp防止分区无法卸载问题
cp -R /mnt/udisk/system /tmp
#启动定时休眠的检测脚本
/opt/bin/php /tmp/system/sleep.php & 

#根据需要显示启动图
if [ -e /mnt/udisk/logo.jpg ];then
   /opt/bin/php /tmp/system/showjpg.php /mnt/udisk/logo.jpg
fi

#将可执行程序复制到/tmp下，防止无法卸载问题
chmod +x /tmp/system/button
#后台执行，防止boot.sh老在内存里
/tmp/system/button  & 


