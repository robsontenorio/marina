# Prepare for get `docker` and `docker-credencial-pass` binaries
FROM docker:28 AS docker-cli
RUN ARCH=$(uname -m) && \
    if [ "$ARCH" = "aarch64" ]; then ARCH="arm64"; else ARCH="amd64"; fi && \
    wget -O docker-credential-pass \
    https://github.com/docker/docker-credential-helpers/releases/download/v0.9.3/docker-credential-pass-v0.9.3.linux-${ARCH} && \
    chmod +x docker-credential-pass

# Base image
FROM robsontenorio/laravel:franken-classic AS base
COPY --from=docker-cli docker-credential-pass /usr/local/bin/docker-credential-pass
COPY --from=docker-cli /usr/local/bin/docker /usr/local/bin/docker
COPY --chown=appuser:appuser . .
RUN gpg --batch --passphrase '' --quick-gen-key appuser default default && pass init appuser


# Production
FROM base AS deploy
RUN chmod a+x .docker/deploy.sh
CMD ["/bin/sh", "-c", ".docker/deploy.sh && start"]
