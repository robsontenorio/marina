#!/bin/bash

set -e

clear

read < /dev/tty -p 'Folder name (ex: myapp): ' folder
read < /dev/tty -p 'Github username: ' ghusername
read < /dev/tty -p 'Github token: ' ghtoken

# docker
if ! docker --version; then
  curl -fsSL https://get.docker.com -o get-docker.sh &&
  sudo sh ./get-docker.sh &&
  sudo systemctl enable docker.service &&
  sudo systemctl enable containerd.service
fi

# https://github.com/Wowu/docker-rollout
mkdir -p ~/.docker/cli-plugins &&
curl https://raw.githubusercontent.com/wowu/docker-rollout/master/docker-rollout -o ~/.docker/cli-plugins/docker-rollout &&
chmod +x ~/.docker/cli-plugins/docker-rollout &&

# Github Registry
export CR_PAT=$ghtoken &&
echo $CR_PAT| docker login ghcr.io -u $ghusername --password-stdin &&


# Create Project
mkdir -p ~/projects/$folder &&
cd ~/projects/$folder &&
touch .env.docker &&
touch docker-compose.yml
