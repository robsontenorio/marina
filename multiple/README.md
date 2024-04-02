# Multiple projects on same server

## Overview

![overview.png](overview.png)

**Guidelines**

- Build the project images using **GitHub Actions** and push to **GitHub Registry**.
- Set up a **Nginx Proxy Manager** to forward the incoming requests to the correct project.
- Use **Watchtower** to deploy automatically new versions of images from your project.
- Each project has **it own** `docker-compose.yml`

**When to use it**

- Projects does not have a lot of traffic.
- You want to save money.


## Pre-requisites

1. A brand-new Cloud VPS.
1. Docker installed inside your VPS.
1. A domain name.


## Sumary

1. Create a VPS
1. Point your domain to VPS
1. Create the skeleton
1. Docker network
1. Private GitHub Registry
1. The proxy project
1. The `docker-compose.yml` anatomy
1. Configure `proxy.mary-ui.com`

## Create a VPS

Create a VPS somewhere and install Docker ... done!

## Point your domain to VPS 

The following example is from a domain registered on Cloudflare .

- The registered domain is `mary-ui.com`
- You can also create subdomains (`flow.mary-ui.com`, `orange.mary-ui.com` ...)
- All of them points to the same IP address of your VPS.

> [!TIP]
> Cloudflare provides the SSL certificate for all domains/subdomains for free. So, you do not need to do anything else on your VPS.

![](domains.png)

## Create the skeleton

Create the following structure for your projects. Notice the folder name reflects the project's domains itself, but it is not mandatory.


SQLite is used on these projects, but you can use any database you want through `docker-compose.yml`.

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

Give correct permission to SQLite database, because we will mount it to the container.

```
chown 1000:1000 database.sqlite
```



## The `docker-compose.yml` anatomy 

- All projects belong to the **same docker network**.
- The **service** name is used to configure the **Nginx Proxy Manager** entries.
- The **container** name is used to configure the **Watchtower**.

```yml
networks:
    default:
        name: mary                        # <--- docker network
        external: true                    # <--- important!

services:    
    myapp:                                # <--- service name (referenced by `Nginx Proxy Manager` )        
        container_name: myapp             # <--- container name (referenced by `Watchtower` )
        image: my-company/myapp:latest
    
    # Other services (optional) ...
    myapp-mysql:
        container_name: myapp-mysql
        image: mysql:8.3        
```

## Docker network

Create a docker network. All projects we will join to this network. Any name you want, but you need to use the same name on all projects.

```bash
docker network create mary
```

## Private GitHub Registry

The following script will authenticate you on the GitHub Registry (private) and store the credentials on docker config file.

Get your GitHub Classic Token [here - TODO](TODO).

```bash
# Replace the variables
export CR_PAT=<YOUR_GITHUB_CLASSIC_TOKEN> &&
echo $CR_PAT| docker login ghcr.io -u <YOUR_GITHUB_USERNAME> --password-stdin
```

## The proxy project

```bash
|   
|__ proxy.mary-ui.com/        
    |
    |__ docker-compose.yml  # <!---- You are here!
```
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
        container_name: mary-proxy
        restart: unless-stopped
        ports:
            - 80:80
            - 81:81
            - 443:443
        volumes:
            - ./data:/data
            - ./letsencrypt:/etc/letsencrypt

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

Now you can access the Nginx Proxy Manager at `http://YOUR-VPS-IP:81`.

---

Actually we run two things here:
- **Nginx Proxy Manager** to forward all incoming traffic to the correct project.
- **Docker Watchtower** to deploy automatically new versions of images from your project.


## Configure `proxy.mary-ui.com`

> [!NOTE]
> There is no need to configure the SSL certificate. Cloudflare will do it for you.

> [!WARNING]
> As we are working with Docker  **always use the service name** you have set on `docker-compose.yml` files to configure the proxy hosts as you will see on the next sections.

- The first one is the `proxy.mary-ui.com` domain to access the **Nginx Proxy Manager** panel.
- After saving, you can access it on `https://proxy.mary-ui.com`

![img_3.png](mary-proxy.png)

## Configure `mary-ui.com`

...