#!/bin/sh
set -e

# Install Docker
echo "\033[1;32m\n\n---\nInstalling Docker ...\n---\033[0m"

curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh ./get-docker.sh

# Enable Docker services
echo "\033[1;32m\n\n---\nEneabling Docker at system startup...\n---\033[0m"
sudo systemctl enable docker.service
sudo systemctl enable containerd.service

echo "\033[1;32m\n\n---\nEneabling Docker Swarm mode ...\n---\033[0m"
# Initialize Docker Swarm
docker swarm init || true

# Create a common network
echo "\033[1;32m\n\n---\nCreateting 'marina' network ...\n---\033[0m"
docker network create --driver overlay marina || true

# Create Docker volume and network
echo "\033[1;32m\n\n---\nCreateting 'marina_data' volume ...\n---\033[0m"
docker volume create marina_data

# Remove Marina if it exists
echo "\033[1;32m\n\n---\nStopping 'marina' service ...\n---\033[0m"
docker service rm marina || true

# Create Marina service
echo "\033[1;32m\n\n---\nStarting 'marina'service ...\n---\033[0m"
docker service create \
    --name marina \
    --network marina \
    --publish 8787:8080 \
    --mount type=volume,source=marina_data,target=/var/www/app/.data \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \
    ghcr.io/robsontenorio/marina:production

echo "\n\n\033[1;32mDocker installed. Swarm mode active. Marina is up at http://SERVER-IP:8787\033[0m"