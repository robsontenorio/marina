# Example: self-hosting with Docker Swarm

A simple approach to host multiple apps on same server using **Docker Swarm**.

![img.png](assets/overview3.png)

## Tools

- **Marina** allows you to deploy stacks and receive deployment webhooks.
- **Traefik**  routes all incoming traffic to the appropriate container.
- **GitHub Actions** build and push images to a registry.
- **Docker Swarm** handles zero-downtime deployments and rollbacks.

## Pre-requisites

- A project on GitHub.
- A brand-new VPS.
- A domain name registered on Cloudflare.
- Be comfortable with Docker and Docker Swarm.
- Be comfortable with GitHub Actions.

## GitHub Actions

Set up a GitHub Action to build and push docker images to the **private GitHub Registry**. See this  [example](template/github/docker-publish.yml) .

```bash
# Github repository

your-user/you-repo         
|   
|__ .docker/
|    |
|    |__ Dockerfile               # Your app Dockerfile
|
|__ .github/
|    |
|    |__ workflows/
|       |
|       |__ docker-publish.yml    # GitHub Action
|               
|__ app/
|__ bootstrap/
|__ database/
|__ ...
```

**IMAGE**

The above GitHub Action example will produce these images:

- `ghcr.io/your-user/your-repo:production`
- `ghcr.io/your-user/your-repo:stage`

**RULES**

- A git tag like `x.y.z` always builds the `production` docker image tag.
- A git tag like `stage-xxxx` always builds the `stage` docker image tag.

**WHY?**

- You need a fixed tag to use on your `docker-compose.yml` files.
- Otherwise, you will need to update the `docker-compose.yml` every time you push a new docker image tag.

## Setup

### VPS

```bash
# Update the system
apt update && apt upgrade

# Install Marina
sh -c "$(curl -fsSL https://github.com/robsontenorio/marina/raw/main/install.sh)"

# Done!
See http://YOUR-VPS-IP-ADDRESS:8787
```

### Proxy

After entering the **Marina** interface, check de `traefik` stack example.
Change the domains and subdomains to your own and **hit deploy**.

### Domains

On your favorite domain registrar, point the domains/subdomains to your VPS IP address.  
We recommend Cloudflare, because it provides the SSL certificates for free.

| Type | Name        | IPV4 Address | Proxy   | TTL  | Observation               |
|------|-------------|--------------|---------|------|---------------------------|
| A    | @           | YOUR-VPS-IP  | Proxied | Auto | your-site.com             |
| A    | **www**     | YOUR-VPS-IP  | Proxied | Auto | **www**.your-site.com     |
| A    | **marina**  | YOUR-VPS-IP  | Proxied | Auto | **marina**.your-site.com  |
| A    | **traefik** | YOUR-VPS-IP  | Proxied | Auto | **traefik**.your-site.com |
| A    | **popcorn** | YOUR-VPS-IP  | Proxied | Auto | **popcorn**.your-site.com |

### Example

Here is an [example](template/stack) of a stack you can use as reference.
In this example, a single stack groups several apps. But, you can organize your stacks as you prefer.

### Credentials

As we are using **private images**, check the **Marina** interface to set the credentials for the private registry.
Otherwise, you will not be able to pull the images.

## Deploying

- Push a new code to the GitHub repository.
- Create a new **git tag**.
- The **GitHub Actions** builds and push images to the **GitHub Private Registry**.
- As the final step of the pipeline, it calls the secret `webhook` URL.
- This will trigger a stack deployment on **Marina**.
- Profit!
