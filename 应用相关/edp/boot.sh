#!/bin/sh

#移除文件共享模块
rmmod g_file_storage 
insmod /lib/g_serial.ko


