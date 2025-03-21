#!/bin/sh
set -e

# Check if Docker is installed
command -v docker >/dev/null && echo -e "\n\e[91m 🚫 Error: Docker is already installed. You can not run this script. \e[0m\n" && exit 1

# Install Docker
echo "\033[96m\n\n\n✨ Installing Docker ...\n\033[0m"

curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh ./get-docker.sh

# Enable Docker services
echo "\033[96m\n\n\n✨ Eneabling Docker at system startup...\n\033[0m"
sudo systemctl enable docker.service
sudo systemctl enable containerd.service

echo "\033[96m\n\n\n✨ Eneabling Docker Swarm mode ...\n\033[0m"
# Initialize Docker Swarm
docker swarm init 

# Create a common network
echo "\033[96m\n\n\n✨ Creating 'marina' network ...\n\033[0m"
docker network create --driver overlay marina 

# Create Docker volume and network
echo "\033[96m\n\n\n✨ Creating 'marina_data' volume ...\n\033[0m"
docker volume create marina_data

# Create Marina service
echo "\033[96m\n\n\n✨ Starting 'marina'service ...\n\033[0m"

docker service create \
    --name marina \
    --network marina \
    --publish 8787:8080 \
    --mount type=volume,source=marina_data,target=/var/www/app/.data \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
    ghcr.io/robsontenorio/marina:production

echo "\n\n\033[1;32m\n✅ Docker installed and swarm mode is active.\033[0m"
echo "\n\033[1;32m\n🚀 Marina is up at http://your-server-ip:8787\n\n\033[0m"