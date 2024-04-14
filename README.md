<p align="center"><img width="250" src="public/images/marina.png"></p>

## Introduction

Marina is dead simple web UI for Docker Swarm.

## Sponsor

Let's keep pushing it, [sponsor me](https://github.com/sponsors/robsontenorio) ❤️

## Follow me

[@robsontenorio](https://twitter.com/robsontenorio)

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

Set the **stacks** path on `.docker/docker-compose.yml`

```yaml
volumes:
    - ../:/var/www/app
    - /var/run/docker.sock:/var/run/docker.sock
    - /path/to/stacks:/var/www/app/stacks             # <--- Change the LEFT SIDE map to your stacks local path
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

**Done! See http://localhost:8717**

## Credentials

- **email**: admin@example.com
- **password**: 2222
