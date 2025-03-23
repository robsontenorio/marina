<p align="center"><img width="250" src="public/images/marina.png"></p>

## Introduction

Marina is a simple self-hosted solution for Docker-based applications.

## Why

My own use case is pretty simple:

- I have a lot of online side projects.
- I do preffer to work with Docker environments.
- Docker based cloud providers are very expensive (see Render.com).
- Docker Swarm allows easy rollout deployments with zero down time.
- I need a simple way to visualize my services and deploy new side projects right the way.

## Install

This command must run only on a **fresh new server**, because it also installs Docker and init the Swarm mode.

```bash
sh -c "$(curl -fsSL https://github.com/robsontenorio/marina/raw/main/install.sh)"
```

**Done!**

See http://SERVER-IP:8787

## Upgrading

Pull the latest image.

```bash
docker pull ghcr.io/robsontenorio/marina:production
```

```bash
docker service update --force marina
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

Start and enter into container.

```bash
cd .docker/ &&
docker-compose up -d &&                  
docker compose exec marina zsh   
```

**Then, inside the running container** ... install, migrate and start the app.

```bash
# See `composer.json`

composer start
```

**Done!** See http://localhost:8787

## Follow me

[@robsontenorio](https://twitter.com/robsontenorio)
