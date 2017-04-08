#!/bin/sh
#我是主启动脚本

#启动定时休眠的检测脚本
/opt/bin/php /mnt/udisk/system/sleep.php & 

#根据需要显示启动图
if [ -e /mnt/udisk/logo.jpg ];then
   /opt/bin/php /mnt/udisk/system/showjpg.php /mnt/udisk/logo.jpg
fi
#执行按钮分析代码,优先执行外置的代码
if [ -e /mnt/udisk/system/button ]; then
   /mnt/udisk/system/button
   /opt/bin/button
else
   #没有外置的再执行内置的
   /opt/bin/button
fi 

