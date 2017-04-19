#!/bin/sh


#必须先把armgcc的路径导入


export PATH=$PATH:/home/tomac/CodeSourcery/Sourcery_G++_Lite/bin/


cd  freetype-2.7

exit;


#php

export CFLAGS=" -I/home/tomac/src/install/include "
export CCFLAGS=" -I/home/tomac/src/install/include "
export LDFLAGS=" -L/home/tomac/src/install/lib "

./configure --host=arm-none-linux-gnueabi \
--prefix=/home/tomac/src/install \
--disable-all  \
--with-zlib-dir=/home/tomac/src/install \
--with-png-dir=/home/tomac/src/install \
--with-jpeg-dir=/home/tomac/src/install \
--with-iconv=/home/tomac/src/install \
--with-freetype-dir=/home/tomac/src/install \
--with-gd=/home/tomac/src/install  \
--disable-cgi \
--disable-phpdbg \
--enable-mbstring \
--enable-pcntl \
--enable-sysvmsg \
--enable-sysvsem \
--enable-sysvshm  \
--enable-zip \
--enable-json

arm-none-linux-gnueabi-strip sapi/cli/php


对 Gd Xpm创建函数的识别做了处理不让识别出来

加入  #include <stdio.h>
expected declaration specifiers or '...' before 'FILE'

在Makefile中将 dynamic 连接的字去掉

pthread 需要使用 -pthread 来替代  -lpthread

#zlib

export CC="arm-none-linux-gnueabi-gcc "
export LD="arm-none-linux-gnueabi-gcc "
export CPP="arm-none-linux-gnueabi-gcc -E "
export AR="arm-none-linux-gnueabi-ar "
export RANLIB="arm-none-linux-gnueabi-ranlib "

./configure --prefix=/home/tomac/src/install -static
make install

exit;


#libpng
export CFLAGS='-I/home/tomac/src/install/include'
export CPPFLAGS='-I/home/tomac/src/install/include'
export LDFLAGS='-L/home/tomac/src/install/lib'

./configure --prefix=/home/tomac/src/install  \
--host=arm-none-linux-gnueabi  \
--enable-shared=no \



#jpeg
./configure --prefix=/home/tomac/src/install  \
--host=arm-none-linux-gnueabi  \
--enable-shared=no \



#freetype
./configure --prefix=/home/tomac/src/install  \
--host=arm-none-linux-gnueabi  \
--enable-shared=no \
--with-png=no 


#gd 




export CFLAGS=' -I/home/tomac/src/install/include -I/home/tomac/src/install/include/freetype2 '
export CPPFLAGS=' -I/home/tomac/src/install/include '
export LDFLAGS='-L/home/tomac/src/install/lib '
export CC="arm-none-linux-gnueabi-gcc "
export LD="arm-none-linux-gnueabi-gcc "
export CPP="arm-none-linux-gnueabi-gcc -E "
export AR="arm-none-linux-gnueabi-ar "
export RANLIB="arm-none-linux-gnueabi-ranlib "


export CFLAGS=
export CPPFLAGS=
export LDFLAGS=
export CC=
export LD=
export CPP=
export AR=
export RANLIB=


./configure --host=arm-none-linux-gnueabi --prefix=/home/tomac/src/install  \
--enable-shared=no \
--with-zlib=/home/tomac/src/install \
--with-freetype=/home/tomac/src/install \
--with-jpeg=/home/tomac/src/install \
--without-fontconfig \
--without-liq \
--without-xpm \
--without-tiff \
--without-webp \
--without-png \


#libXpm
./configure --prefix=/home/tomac/src/install  \
--host=arm-none-linux-gnueabi  \



#libX11

./configure --prefix=/home/tomac/src/install  \
--host=arm-none-linux-gnueabi  \
--disable-loadable-xcursor \
--disable-xthreads \
--disable-xcms \
--disable-xlocale \
--disable-xkb 



--disable-xf86bigfont \

#bigreqsproto



#libiconv
./configure --prefix=/home/tomac/src/install  \
--host=arm-none-linux-gnueabi  \
--enable-static=yes \
--enable-shared=no 


