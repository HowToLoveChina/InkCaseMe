#!/bin/sh

#取得键码
key=$1
#将当前时间写入内存文件
date +%s > /tmp/keystamp

#取得当前的应用
app=`cat /mnt/udisk/app.txt`
#根据不同的应用分发给程序
if [ x$1 == x28 ]; then
	/opt/bin/php /mnt/udisk/${app}/${app}.php n
	exit
fi

if [ x$1 == x63 ]; then
	/opt/bin/php /mnt/udisk/${app}/${app}.php p
	exit
fi

if [ x$1 == x33 ]; then
fi


if [ x$1 == x66 ]; then
fi

