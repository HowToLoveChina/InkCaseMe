#!/bin/sh
#make V=s ARCH=arm CROSS_COMPILE=/home/tomac/CodeSourcery/Sourcery_G++_Lite/bin/arm-none-linux-gnueabi- clean
make V=s ARCH=arm CROSS_COMPILE=/home/tomac/CodeSourcery/Sourcery_G++_Lite/bin/arm-none-linux-gnueabi- modules

vi drivers/usb/gadget/g_ether.ko

rm /media/tomac/ROCK-CHIPS/g_ether.ko ; 
cp drivers/usb/gadget/g_ether.ko  /media/tomac/ROCK-CHIPS/
sync
sync

