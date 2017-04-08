#!/bin/sh

if [ -f /mnt/udisk/usbtty ]; then
  /sbin/getty /dev/ttygserial -L 115200 vt100
  sleep 1
else
  sleep 3600
fi

