#!/bin/sh
set -e

clear

read 'Folder name (ex: myapp): ' folder
read 'Github username: ' ghusername
read 'Github token: ' ghtoken

# docker
#curl -fsSL https://get.docker.com -o get-docker.sh &&
#sudo sh ./get-docker.sh &&
#sudo systemctl enable docker.service && 
#sudo systemctl enable containerd.service && 

# Github Registry
export CR_PAT=$ghtoken &&
echo $CR_PAT| docker login ghcr.io -u $ghusername --password-stdin && 

# https://github.com/Wowu/docker-rollout
#mkdir -p ~/.docker/cli-plugins &&
#curl https://raw.githubusercontent.com/wowu/docker-rollout/master/docker-rollout -o ~/.docker/cli-plugins/docker-rollout && 
#chmod +x ~/.docker/cli-plugins/docker-rollout &&

# Project Ping17
mkdir -p ~/projects/$folder &&
cd ~/projects/$folder && 
touch .env.docker && 
touch docker-compose.yml
