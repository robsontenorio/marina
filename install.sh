#!/bin/sh
set -e

# Install Docker
echo -e "\n\n---\033[1;32mInstalling Docker\n---\033[0m"

curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh ./get-docker.sh

# Enable Docker services
echo -e "\n\n---\033[1;32mEneable Docker at system startup\n---\033[0m"
sudo systemctl enable docker.service
sudo systemctl enable containerd.service

echo -e "\n\n---\033[1;32mEnable Docker Wwarm mode\n---\033[0m"
# Initialize Docker Swarm
if ! docker info | grep -q "Swarm: active"; then
    docker swarm init
fi

# Create a common network
echo -e "\n\n---\033[1;32mCreate 'marina' network\n---\033[0m"
docker network create --driver overlay marina

# Create Docker volume and network
echo -e "\n\n---\033[1;32mCreate 'marina_data' volume\n---\033[0m"
docker volume create marina_data

# Create Marina service
echo -e "\n\n---\033[1;32mStarting 'marina'service ...\n---\033[0m"
docker service create \
    --name marina \
    --network marina \
    --publish 8787:8080 \
    --mount type=volume,source=marina_data,target=/var/www/app/.data \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
    ghcr.io/robsontenorio/marina:production

echo -e "\n\n\033[1;32mDocker installed. Swarm mode active. Marina is up at http://SERVER-IP:8787\033[0m"