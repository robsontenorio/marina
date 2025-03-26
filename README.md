<p align="center"><img width="250" src="public/images/marina.png"></p>

## Introduction

Marina is a simple self-hosting solution using Docker Swarm. It's a web interface to manage containers and services.

## Warning

The web interface does not include advanced features such as volume and network management.

It is simply a way to deploy stacks based on `docker-compose.yaml` files and visualize containers in a Docker Swarm environment.

## Install

This command must run only on a **fresh new server**, because it installs Docker and init the Swarm mode.

```bash
sh -c "$(curl -fsSL https://github.com/robsontenorio/marina/raw/main/install.sh)"
```

**Done!** See http://SERVER-IP:8787

## Upgrading

```bash
docker service update --force --image ghcr.io/robsontenorio/marina:production marina
```

## Contributing

Clone the repository.

```bash
git clone git@github.com:robsontenorio/marina.git
```

Create `.env` from `.env.example` and adjust few vars.

```bash
APP_ENV=local
APP_DEBUG=true
```

Start and access the container.

```bash
cd .docker/ && docker-compose up -d && docker compose exec marina zsh   
```

**Inside the running container** ... install, migrate and start the app.

```bash
# See `composer.json`

composer start
```

**Done!** See http://localhost:8787

## Follow me

[@robsontenorio](https://twitter.com/robsontenorio)
