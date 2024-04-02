# Multiple projects on same server

This document describes how to set up multiple projects on the same server using Docker.

## When to use it

- Projects does not have a lot of traffic.
- You want to save money.


## Pre-requisites

- A project on GitHub.
- A VPS with Docker installed.
- A domain name registered on Cloudflare.


## Overview

![overview.png](overview.png)

**Guidelines**

- Set the **Nginx Proxy Manager** to forward the traffic to the correct project.
- Build the project images using **GitHub Actions** and push to **GitHub Registry**.
- Use **Watchtower** to deploy automatically new versions of images from your project.
- Each project has **it own** `docker-compose.yml` 

## Base Docker image

For reference, all the projects here use the same base image `robsontenorio/laravel` that includes a Nginx server bound to port `8080`.  
Of course, you can use any base image you want.


## The skeleton

The following folder structure on your **VPS** represents the sites you want to deploy.

```bash
YOUR VPS
|   
|__ proxy.mary-ui.com/        # Nginx Proxy Manager + Watchtower
|   |
|   |__ docker-compose.yml
|
|__ mary-ui.com/              # Project 1
|   |
|   |__ .env   
|   |__ database.sqlite
|   |__ docker-compose.yml
|
|__ flow.mary-ui.com/         # Project 2
|   |
|   |__ .env   
|   |__ database.sqlite
|   |__ docker-compose.yml
|
|__ orange.mary-ui.com/       # Project 3
   |
   |__ .env   
   |__ database.sqlite
   |__ docker-compose.yml
```

## Repositories

Although you can use any name you want, we named the repositories to match the site we want to deploy.  
Notice that we will not pull directly these repositories into the **VPS**, it js just a name convention.

![repositories.png](repositories.png)

## GitHub Actions
Set up a GitHub Action on your repository to build the project Docker images and push them to the **Private GitHub Registry**.

```bash
robsontenorio/mary-ui.com         # Github repository
|   
|__ .docker/
|    |
|    |__ Dockerfile                   
|
|__ .github/
|    |
|    |__ workflows/
|       |
|       |__ docker-publish.yml    # <-- You are here!
|               
|__ app/
|__ bootstrap/
|__ database/
|__ ...
``` 

**Approach**
- If you push git a tag like `x.y.z`, build the `production` docker image tag.
- If you push git a tag like `stage-xxxx`, build the `stage` docker image tag.

**Why?**
- You need a fixed tag to use on the `docker-compose.yml` files.
- Otherwise, you will need to update the `docker-compose.yml` file every time you push a new image.

**Images**  

This following GitHub Action will produce this images that will be used on the `docker-compose.yml` files.
- `ghcr.io/robsontenorio/mary-ui.com:production` 
- `ghcr.io/robsontenorio/mary-ui.com:stage`

**.github/workflows /docker-publish.yml**

```yml
name: Create and publish a Docker image

on:
  push:
    tags:
      - '[0-9]+.[0-9]+.[0-9]+'        # any `x.y.z` tag builds the `production` image
      - 'stage-*'                     # the `stage-xxxx` pattern tag builds the`stage` image

env:
  REGISTRY: ghcr.io
  IMAGE_NAME: ${{ github.repository }}

jobs:
  build-and-push-image:
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: "Log in to the Container registry"
        uses: docker/login-action@v3.1.0
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: "Check Github Tag"
        id: check-tag
        run: |
          if [[ ${{ github.event.ref }} =~ ^refs/tags/[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
              echo "IS_PRODUCTION=true" >> $GITHUB_OUTPUT
          fi

          if [[ ${{ github.event.ref }} =~ ^refs/tags/stage-(.*)$ ]]; then
              echo "IS_STAGE=true" >> $GITHUB_OUTPUT
          fi

      - name: "Extract Docker metadata (tags, labels)"
        id: meta
        uses: docker/metadata-action@v5.5.1
        with:
          images: ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}
          flavor: |
            latest=false
          tags: |
            type=raw,value=production,enable=${{  steps.check-tag.outputs.IS_PRODUCTION == 'true' }}
            type=raw,value=stage,enable=${{  steps.check-tag.outputs.IS_STAGE == 'true' }}

      - name: "Build and push Docker images"
        uses: docker/build-push-action@v5.3.0
        with:
          context: .
          file: .docker/Dockerfile
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
```

## Point your domains to VPS 

The following example is from a domain registered on Cloudflare .

- The registered domain is `mary-ui.com`
- You can also create subdomains (`flow.mary-ui.com`, `orange.mary-ui.com` ...)
- All of them points to the same IP address of your **VPS**.


![](domains.png)

> [!TIP]
> Cloudflare provides the SSL certificate for all domains/subdomains for free. So, you do not need to do anything else on your VPS.





## Docker network

Create a docker network. All projects must join to this network. So, you need to use the same name on all projects.

```bash
docker network create mary
```



## The proxy project

Actually we set up two things here:
- **Nginx Proxy Manager** to forward all incoming traffic to the correct project.
- **Watchtower** to deploy automatically new versions of images from your project.


```bash
YOUR VPS
|   
|__ proxy.mary-ui.com/        # <!---- You are here!  
    |
    |__ docker-compose.yml  
```

**docker-compose.yml**

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
    
    mary-watchtower:
        image: containrrr/watchtower
        container_name: mary-watchower
        # Use the `container_name` (not service name) of the projects you want to watch
        command: mary-app flow-app orange-app  --log-level error --interval 5 --rolling-restart
        volumes:
            - /var/run/docker.sock:/var/run/docker.sock
            - /root/.docker/config.json:/config.json
```

**Run it**

After started, you can access the Nginx Proxy Manager at `http://YOUR-VPS-IP-NUMBER:81`

```
docker-compose up -d
```

Now, configure `proxy.mary-ui.com` domain to access the **Nginx Proxy Manager** panel. 
After saving, you can access it on `https://proxy.mary-ui.com`

![img_3.png](mary-proxy.png)

> [!WARNING]
> There is no need to configure the SSL certificate. Cloudflare will do it for you.

> [!WARNING]
> Notice the scheme is always `http`

> [!WARNING]
> As we are working with Docker  **always use the service name and the port** you have set on `docker-compose.yml` files to configure the proxy entries.

## Private GitHub Registry

Our images were pushed to the GitHub Registry using GitHub Actions. So, you need to authenticate  in to the registry on your **VPS** to pull the images.

The following script will authenticate you on the Private GitHub Registry and store the credentials on docker config file.

[How to get a GitHub Classic Token?](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens#creating-a-personal-access-token-classic)

```bash
# Replace the variables

export CR_PAT=<YOUR_GITHUB_CLASSIC_TOKEN> &&
echo $CR_PAT| docker login ghcr.io -u <YOUR_GITHUB_USERNAME> --password-stdin
```

## Configure `mary-ui.com`
Create the following files.

```bash
|__ mary-ui.com/                # <!---- You are here! 
    |
    |__ .env                
    |__ docker-compose.yml
    |__ database.sqlite
```
**.env**

```bash
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:....
APP_URL=http://mary-ui.com
```

**docker-compose.yml**

> [!WARNING]
> Do not expose any port here!

```yml
networks:
  default:
    name: mary
    external: true

services:
    mary-app:                                                   # <-- referenced by `Nginx Proxy Manager`
        container_name: mary-app                                # <-- referenced by `Watchtower`
        image: ghcr.io/robsontenorio/mary-ui.com:production     # <-- Use fixed `production` tag
        restart: always
        pull_policy: always
        env_file:
          - .env
        volumes:
  	      - ./database.sqlite:/var/www/app/database/database.sqlite
```

**SQLite**
> [!WARNING]
> Give correct permission to SQLite database, because we will mount it to the container.

```
chown 1000:1000 database.sqlite
```

**Configure the proxy**

