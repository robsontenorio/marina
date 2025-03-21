#!/bin/sh
set -e

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh ./get-docker.sh

# Enable Docker services
sudo systemctl enable docker.service
sudo systemctl enable containerd.service

# Initialize Docker Swarm
docker swarm init

# Create a common network
docker network create --driver overlay marina

# Create Docker volume and network
docker volume create marina_data

# Create Marina service
docker service create \
    --name marina \
    --network marina \
    --publish 8787:8080 \
    --mount type=volume,source=marina_data,target=/var/www/app/.data \
    --mount type=bind,source=/var/run/docker.sock,target=/var/run/docker.sock \    
    ghcr.io/robsontenorio/marina:production