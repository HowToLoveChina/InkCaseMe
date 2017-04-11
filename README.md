# InkCaseMe


（推荐下载）原版固件：https://wiztrader.ctfile.com/fs/dCd197387966
（推荐下载）DIY固件：修正双重启动等已知问题 20170410 https://wiztrader.ctfile.com/fs/PAs197383276
       （过时）Windows带usbtty功能  20170406 https://wiztrader.ctfile.com/fs/hrS196017989


本教程Windows刷机部分由tomac撰写，供具有一定Linux使用知识的人使用。本人不承担任何相关责任。
   请从下载下载刷机工具包 原版固件和DIY固件 

1.连接USB以后长按设备上的按钮，运行原版固件中的AndroidTool ，先按"低格"，再点"执行"，待设备重启后执行下步。

2.再次长按设备，运行DIY固件中的AndroidTool 在列表界面右击选择导入配  => 上级目录 => rockdev => Images => newsys 

3.点击执行。如果顺利就刷好了。如果遇到system校验失败，可以把上面的勾都去掉，继续刷user，在高级中“重启设备”，可能就好了。

4.如果重启后，不显示女性图片，连接电脑有两个盘符，请重刷 system 和 user 。只出现一个请重刷user .

5.多次重刷还是有问题，请转1再来。


正常执行的设备，应该是显示一个女性的画面，可自行更改U盘中的logo.jpg 

系统用最好的语言PHP为基础

电纸书请把要看的TXT保存成  UTF8格式，然后复制到设备的ebook目录下，文件名为book.txt 。单击为看下页，长按是上页



常见问题请移步：https://github.com/HowToLoveChina/InkCaseMe/wiki


** 应用编写原理 ** 

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



** 系统运行流程 **
# /etc/init.d/rcS
1.   检查system user 是否正常 ，如果不可用，那么通过 g_file_storage 把这两个区挂出来，供用户在操作系统里刷写

2.   检查有没有 /mnt/udisk/usbtty 如果有，那么不挂U盘，变成usb串口，

3.   检查有没有 /mnt/udisk/update.sh 如果有，那么更名为  _update.sh 并执行。完成后重启

4.   检查有没有 /mnt/udisk/system/boot.sh 如果有，那么执行，否则执行  /opt/etc/rc.local


** 待机处理 **

在 /mnt/udisk/system/sleep.php 定期检查有没有按键，如果长时间没有按键，进入standby模式，待机12小时也不少1%的电。


** 按键处理 **

/mnt/udisk/system/boot.sh 中最后启动  button 程序，如果有按键，将键值交给 key.sh 

key.sh 将 

单击转换成 n 参数交给应用

长按转换成 p 参数交给应用

双击转换成 d 参数交给应用

待机唤醒事件 转换成 n 参数交给应用 


