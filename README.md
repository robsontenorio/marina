# joe



Update linux and reboot.   
Wait server reboot before SSH it again
```bash
DEBIAN_FRONTEND=noninteractive &&
sudo apt update && 
apt upgrade -y &&
reboot
```


Install
```
curl -fsSL https://raw.githubusercontent.com/robsontenorio/joe/main/install.sh | bash
```
