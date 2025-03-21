<p align="center"><img width="250" src="public/images/marina.png"></p>

## Introduction

Marina is dead simple web UI for Docker Swarm.

## Features

- [x] Monitor resources
- [x] Create stacks
- [x] Edit stacks
- [x] Update services
- [x] Remove services
- [x] Scale services
- [x] View logs
- [ ] Manage Networks
- [ ] Manage Volumes
- [ ] Manage Secrets
- [ ] Manage Configs
- [ ] Manage Nodes

## Sponsor

Let's keep pushing it, [sponsor me](https://github.com/sponsors/robsontenorio) ❤️

## Follow me

[@robsontenorio](https://twitter.com/robsontenorio)

## Install

Marina should be installed on a fresh new server.  
This command also installs Docker and init the Swarm mode. 

```bash
sh -c "$(curl -fsSL https://github.com/robsontenorio/marina/raw/master/install.sh)"
```

**Done!**

See http://localhost:8787 or http://SERVER-IP:8787.

## Upgrading

Pull the latest image.

```bash
docker pull ghcr.io/robsontenorio/marina:production
```


```bash
docker service update --force marina
```

Run it again.

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
docker compose exec marina-app zsh   
```

**Then, inside the running container** ... install, migrate and start the app.

```bash
# See `composer.json`

composer start
```

**Done!** See http://localhost:8787

## Credentials

- **email**: admin@example.com
- **password**: 2222
