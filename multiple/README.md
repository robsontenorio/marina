# Multiple projects on same server

## Overview

**Guidelines**

- We have a **proxy manager** that will route the requests to the correct project.
- All projects are on the same **docker network**.
- Each project runs **isolated** from others.

**When to use it**

- Projects does not have a lot of traffic.
- You want to save money.


## Pre-requisites

1. A brand-new Cloud VPS.
1. Docker installed inse your VPS.
1. A domain name.


## VPS

Create a VPS somewhere and install Docker on it.

- Digital Ocean 
- Hetzner
- Hostinger 
- ...

## Domain 

The following example is from a domain registered on Cloudflare .

- The registered domain is `mary-ui.com`
- Create some subdomains (`flow.mary-ui.com`, `orange.mary-ui.com` ...)
- All of them points to the same IP address of your VPS.

![](domains.png)

Cloudflare provides the SSL certificate **for free** for all domains/subdomains. So, you do not need to do anything else on your VPS.

## Structure

Create the following structure for each project with empty files. Notice the folder name reflects the project domains itself, but it is not mandatory.

```bash
|   
|__ proxy.mary-ui.com/        # Proxy project
|   |
|   |__ docker-compose.yml
|
|__ mary-ui.com/              # Project 1
|   |
|   |__ docker-compose.yml
|   |__ .env
|   |__ database.sqlite
|
|__ flow.mary-ui.com/         # Project 2
|   |
|   |__ docker-compose.yml
|   |__ .env
|   |__ database.sqlite
|
|__ orange.mary-ui.com/       # Project 3
   |
   |__ docker-compose.yml
   |__ .env
   |__ database.sqlite   
```

Give correct permission to Sqlite database, because we will mount it to the container.

```
chown 1000:1000 database.sqlite
```

## Docker network

Create a docker network. All projects we will join to this network. Any name you want, but you need to use the same name on all projects.

```bash
docker network create mary
```

## Mandatory anatomy 

- All projects belong to the **same docker network**.
- The **service** name is used to configure the **proxy manager**.
- The **container** name is used to configure the **watchtower**.

```yml
networks:
    default:
        name: mary                        # <--- The docker network
        external: true

services:    
    myapp:                                # <--- Service name
        image: my-company/myapp:latest
        container_name: myapp             # <--- Container name
    
    # Other services (optional) ...
    myapp-mysql:
        image: mysql:8.3
        container_name: myapp-mysql
```

## The proxy project

```bash
|   
|__ proxy.mary-ui.com/        
    |
    |__ docker-compose.yml  # <!---- You are here!
```

Actually we will run two things here:
- **Nginx Proxy Manager** to redirect all incoming traffic to the correct project. 
- **Docker Watch Tower** to deploy automatically new versions of images from your project.

---
<details>
<summary>Click to see the docker-compose.yml</summary>

```yml
networks:
    default:
        name: mary
        external: true

services:

    ####### NGINX PROXY ##########
  
    mary-proxy:
        #image: jc21/nginx-proxy-manager:latest (TODO)
        image: jc21/nginx-proxy-manager:github-pr-3478
        container_name: ping17-proxy
        restart: unless-stopped
        ports:
            - 80:80
            - 81:81
            - 443:443
        volumes:
            - ./proxy.mary-ui.com/data:/data
            - ./ping17.com/letsencrypt:/etc/letsencrypt

    ######## WATCHTOWER ########
    
    watchtower:
        image: containrrr/watchtower
        container_name: ping17-watchower
        # Place here all `container_name` of the upcoming projects you want to watch.
        command: mary-app flow-app ping-app orange-app paper-app  --log-level error --interval 5 --rolling-restart
        volumes:
            - /var/run/docker.sock:/var/run/docker.sock
            - /root/.docker/config.json:/config.json
```
</details>

---

**Run it**

```
docker-compose up -d
```

**Configure the first proxy**

After this point you are on the Docker "land", so  **always use the service name** to configure the proxy hosts.

- You can access the Nginx Proxy Manager at `http://YOUR-VPS-IP:81`.  
- The first one is the `proxy.mary-ui.com` domain to access the proxy panel.
- After saving you can access it on `https://proxy.mary-ui.com`

![img_3.png](mary-proxy.png)
