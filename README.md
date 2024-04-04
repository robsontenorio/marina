# Joe

**Self-hosting with Docker**

This is not a tool, it is just an approach on how to deploy projects with Docker.   
With self-hosting you can save money and have more control over your data.

## Tools

1. **GitHub Actions** to build and push images. 
1. **Private GitHub Registry** to store Docker images.
1. **Nginx Proxy Manager** to redirect all incoming traffic to the right container.
1. **Watchtower** to update containers automatically when you push a new version of the image.

## Approaches

1. [A single project on a single server](single/README.md).
1. [Multiple projects on a single server](multiple/README.md).
2. 1. [Docker Swarm](swarm/README.md).
