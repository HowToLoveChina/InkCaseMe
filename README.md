# InkCaseMe

简介:InkCase i5  (拆机图请移步：http://bbs.mydigit.cn/read.php?tid=2006268 )

经过大家的努力，现在已经可以做为独立的电纸书来使用。

目前的功能有：

1. 电纸书应用 ebook  作者：索马里的海贼(QQ:3298302054) , wushy 贡献菜单功能
2. 图片浏览   jpg 

主要刷机资料：
1. (推荐下载) 图文刷机教程：https://wiztrader.ctfile.com/fs/FfC198007421 （感谢作者：天気雨 QQ:727999552）
2. (推荐下载) DIY固件：最新二键刷机包 20170420 https://wiztrader.ctfile.com/fs/SNY199110377
3. (过时)20170419 https://wiztrader.ctfile.com/fs/SAI199098409
4. (过时) 修正双重启动等已知问题 20170410 https://wiztrader.ctfile.com/fs/PAs197383276
5. (过时）Windows带usbtty功能  20170406 https://wiztrader.ctfile.com/fs/hrS196017989
6. (过时）原版固件：https://wiztrader.ctfile.com/fs/dCd197387966

更新历史:
1. 2017.04.06 自制固件出炉
2. 2017.04.07 开放usbtty功能
3. 2017.04.10 电纸书功能可用
4. 2017.04.12 天気雨制作刷机教程
5. 2017.04.19 wushy增加菜单功能;解决图片显示异常;引入更新模式图片，待机图片，关机图片
6. 2017.04.20 修正时序引起的待机图片异常

使用中主要注意事项:
1. 为防止脚本异常，所以固件在非usbtty模式下，默认直接挂载U盘功能，所以连接电脑时，！！一定！！按以下步骤才能正常更新
   a.先插拔一次，让系统进入合理的更新模式
   b.再次插入，这时脚本系统接管U盘，可正常更新
   c.卸载U盘，拔出USB线
   d.无需再重启，可直接进入使用状态




本教程Windows刷机部分由HowToLoveChina撰写，。本人不承担任何相关责任。

请从前述地址下载二键刷机工具包（DIY固件） 

1. 连接USB以后长按设备上的按钮。
2. 如果没有安装过驱动，请定位到刷机包中相应目录，安装驱动
3. 运行DIY固件中的AndroidTool 。界面下方应提示发现LOADER设备。
4. 先按"低格"，再点"执行"，应可以正常重启，即刷机完成。
5. 如果顺利就刷好了。如果遇到system校验失败，可以把上面的勾都去掉，继续刷user，在高级中“重启设备”，可能就好了。
6. 如果重启后，不显示女性图片，连接电脑有两个盘符，请重刷 system 和 user 。只出现一个请重刷user .
7. 多次重刷还是有问题，请转1再来。

注：WIN10操作系统的用户 请访问 https://github.com/HowToLoveChina/InkCaseMe/tree/master/%E5%B8%B8%E8%A7%81%E8%A7%A3%E5%86%B3%E6%96%B9%E6%A1%88/2%E3%80%81WIN10%E9%A9%B1%E5%8A%A8%E4%B8%8B%E8%BD%BD  下载相应的驱动。

正常执行的设备，应该是显示一个女性的画面，可自行更改U盘中的logo.jpg 

系统用最好的语言PHP为基础

电纸书请把要看的TXT保存成UTF8格式，然后复制到设备的ebook目录下。单击为看下页，长按是上页，双击出菜单选书



常见问题请移步：https://github.com/HowToLoveChina/InkCaseMe/wiki


# 应用编写原理 

根目录的 app.txt 存放当前设备的工作应用名称，比如 test 

创建同名目录 test  ，内放置  test.php 即可。

php请按以下规则编写

$im = imagecreatetruecolor( 360 , 600 );

....中间处理代码....

imagefile($im,"/dev/fb" , 1 );

需要说明的是imagefile这个函数是本系统的自定义函数，需要在电脑上调测时，请自行编写

function imagefile($im,$file,$mode){
   imagepng($im); 
}

这样在浏览器中输出，即可实现本地测试。



# 系统运行流程 

** /etc/init.d/rcS **
1.   检查system user 是否正常 ，如果不可用，那么通过 g_file_storage 把这两个区挂出来，供用户在操作系统里刷写

2.   检查有没有 /mnt/udisk/usbtty 如果有，那么不挂U盘，变成usb串口，

3.   检查有没有 /mnt/udisk/update.sh 如果有，那么更名为  _update.sh 并执行。完成后重启

4.   检查有没有 /mnt/udisk/system/boot.sh 如果有，那么执行，否则执行  /opt/etc/rc.local


# 待机处理 

在 /mnt/udisk/system/sleep.php 定期检查有没有按键，如果长时间没有按键，进入standby模式，待机12小时也不少1%的电。


# 按键处理 

/mnt/udisk/system/boot.sh 中最后启动  button 程序，如果有按键，将键值交给 key.sh 

key.sh 将 

单击转换成 n 参数交给应用

长按转换成 p 参数交给应用

双击转换成 d 参数交给应用

待机唤醒事件 转换成 n 参数交给应用 


