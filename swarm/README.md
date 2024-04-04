# Docker Swarm 

Simple approach to deploy multiple Laravel projects using Docker Swarm on same server.

[TODO IMAGE]

## Final result

```bash
YOUR_VPS
|   
|__ .env.mary
|__ .env.flow
|__ .env.orange
|__ # ...
|__ # ...                      
|__ docker-compose.yml
```

## Install Docker + Swarm mode

Set up this on your **VPS**. 

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh &&
sudo sh ./get-docker.sh &&

# Enable on startup
sudo systemctl enable docker.service &&
sudo systemctl enable containerd.service &&

# Init Swarm
docker swarm init
```
## GitHub Private Registry

You need a GitHub Classic Token. This is required to pull images from GitHub Private Registry.

```bash
export CR_PAT=<REGISTRY_TOKEN> &&
echo $CR_PAT| docker login ghcr.io -u <USERNAME> --password-stdin 
```

## Network
All services will join to this network.

```bash
docker network create -d overlay mary
```

## Volumes

Create all needed volumes for each service.

```bash
# Apps
docker volume create mary-db &&
docker volume create paper-db &&
docker volume create orange-db &&
docker volume create flow-db &&
docker volume create ping-db 

# Proxy
docker volume create mary-proxy-data &&
docker volume create mary-proxy-letsencrypt
```

## Env files

Create a `.env.xxxx` file for each service.

```bash
YOUR_VPS
|   
|__ .env.mary
|__ .env.flow
|__ .env.orange
|__ # ...
|__ # ...                      
|__ docker-compose.yml
```
```bash
# .env.mary
APP_URL=https://mary-ui.com
APP_ENV=production
APP_DEBUG=false
APP_KEY=...
``` 

```bash
# .env.flow
APP_URL=https://flow.mary-ui.com
APP_ENV=production
APP_DEBUG=false
APP_KEY=...
```


## Compose file
```yaml
networks:
  default:
    name: mary
    external: true

volumes:  
  mary-db:
    external: true
  paper-db:
    external: true
  orange-db:
    external: true
  flow-db:
    external: true
  ping-db:
    external: true
  mary-proxy-data:
    external: true
  mary-proxy-letsencrypt:
    external: true

services:

  ####### PROXY ##########
  mary-proxy:
    #image: jc21/nginx-proxy-manager:latest
    image: jc21/nginx-proxy-manager:github-pr-3478
    ports:
      - 80:80
      - 81:81
      - 443:443
    volumes:
      - mary-proxy-data:/data
      - mary-proxy-letsencrypt:/etc/letsencrypt

  ######## MARY ########
  mary-app:
    image: ghcr.io/robsontenorio/ping17.com:production
    env_file:
      - .env.mary
    volumes:
      - mary-db:/var/www/app/database/
    healthcheck:
      test: [ "CMD", "curl", "-f", "http://localhost:8080/up" ]
      interval: 5s
      timeout: 10s
      retries: 2
    deploy:
      update_config:
        delay: 1s
        order: start-first
        failure_action: continue
      rollback_config:
        order: start-first

  ######## ORANGE ########
  orange-app:
    image: ghcr.io/robsontenorio/orange.mary-ui.com:production
    env_file:
      - .env.orange
    volumes:
      - orange-db:/var/www/app/database/
    healthcheck:
      test: [ "CMD", "curl", "-f", "http://localhost:8080/up" ]
      interval: 5s
      timeout: 10s
      retries: 2
    deploy:
      update_config:
        delay: 1s
        order: start-first
        failure_action: continue
      rollback_config:
        order: start-first

  ######## PAPER ########
  paper-app:
    image: ghcr.io/robsontenorio/paper.mary-ui.com:production
    env_file:
      - .env.paper
    volumes:
      - paper-db:/var/www/app/database/
    healthcheck:
      test: [ "CMD", "curl", "-f", "http://localhost:8080/up" ]
      interval: 5s
      timeout: 10s
      retries: 2
    deploy:
      update_config:
        delay: 1s
        order: start-first
        failure_action: continue
      rollback_config:
        order: start-first

  ######## FLOW ########
  flow-app:
    image: ghcr.io/robsontenorio/flow.mary-ui.com:production
    env_file:
      - .env.flow
    volumes:
      - flow-db:/var/www/app/database/
    healthcheck:
      test: [ "CMD", "curl", "-f", "http://localhost:8080/up" ]
      interval: 5s
      timeout: 10s
      retries: 2
    deploy:
      update_config:
        delay: 1s
        order: start-first
        failure_action: continue
      rollback_config:
        order: start-first

  ######## PING ########
  ping-app:
    image: ghcr.io/robsontenorio/ping.mary-ui.com:production
    env_file:
      - .env.ping
    volumes:
      - ping-db:/var/www/app/database/
    healthcheck:
      test: [ "CMD", "curl", "-f", "http://localhost:8080/up" ]
      interval: 5s
      timeout: 10s
      retries: 2
    deploy:
      update_config:
        delay: 1s
        order: start-first
        failure_action: continue
      rollback_config:
        order: start-first
```

## Zero downtime deployments and rollback

These configs make zero downtime deployments and rollback possible, 
when you need to update the stack or update images from the services. 

```yaml
# This is configured for each service on `docker-compose.yml`

healthcheck:
  # ...
deploy:
  update_config:
    # ...
  rollback_config:
    # ...
```

## Stack
This term `stack` refers to a group of services that are defined in a `docker-compose.yml` file.

## Deploy the stack

Think it as a `docker-compose up` command, but for Swarm.

```yaml
docker stack deploy [OPTIONS] [STACK_NAME] [EXTRA]
```

```bash 
#                [output progress]                  [file]        [any name]    [private registry]
#                         |                            |              |              |         
                                
docker stack deploy --detach=false --compose-file docker-compose.yml mary --with-registry-auth
```
> [!INFO ]
> If you change any service in `docker-compose.yml` file, you need to re-deploy the stack using the same command above again.



## Point your domains to the VPS

- The root registered domain is `mary-ui.com`
- Make sure to create an extra `proxy` subdomain.
- You can also create subdomains.
- Point all of them to the same IP address of your **VPS**.


![](domains.png)

