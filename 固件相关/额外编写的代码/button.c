#include <stdio.h>
#include <linux/input.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>

#define DEV_PATH "/dev/input/event0"   //difference is possible  

int main()
{
    int keys_fd;
    char ret[2];
	char cmd[128];
    struct input_event t;
    keys_fd=open(DEV_PATH, O_RDONLY);
    if(keys_fd <= 0)
    {
        printf("open /dev/input/event2 device error!\n");
        return -1;
    }
    while(1)
    {
        if(read(keys_fd, &t, sizeof(t)) == sizeof(t))
        {
            if(t.type==EV_KEY)
                if(t.value==1)
                {
                    printf("key %d pressed!\n", t.code);
					sprintf(cmd,"/bin/sh /mnt/udisk/system/key.sh %d",t.code);
					system(cmd);
/*
					switch(t.code){
						case KEY_ESC:
							system("/bin/sh /mnt/udisk/system/key.sh  66 ");
							break;
						case 28:
                    if(t.code == KEY_ESC)
                        break;
                    if(t.code == 28 ){ //按一次
                    	system("/opt/bin/php /mnt/udisk/ebook.php n");
                    }
                    if(t.code == 63){//双击
                    	system("/opt/bin/php /mnt/udisk/ebook.php p");
                    }
*/
                }
        }
    }
    close(keys_fd);
    return 0;
}
