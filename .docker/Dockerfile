FROM docker:28 AS docker-cli
RUN ARCH=$(uname -m) && \
    if [ "$ARCH" = "aarch64" ]; then ARCH="arm64"; else ARCH="amd64"; fi && \
    wget -O docker-credential-pass \
    https://github.com/docker/docker-credential-helpers/releases/download/v0.9.3/docker-credential-pass-v0.9.3.linux-${ARCH} && \
    chmod +x docker-credential-pass

# Base image
FROM robsontenorio/laravel:3.5 AS base
USER root
COPY --from=docker-cli docker-credential-pass /usr/local/bin/docker-credential-pass
RUN apt update && apt install -y pass && rm -rf /var/lib/apt/lists/*
USER appuser
COPY --from=docker-cli /usr/local/bin/docker /usr/local/bin/docker
COPY --chown=appuser:appuser . .
RUN gpg --batch --passphrase '' --quick-gen-key appuser default default && pass init appuser

# For local development
FROM base AS local
CMD ["/usr/local/bin/start"]

# Used to build the final image for distribution
FROM base AS deploy
RUN composer install --prefer-dist --no-interaction --no-progress --ansi
RUN yarn install && yarn build
RUN chmod a+x .docker/deploy.sh
CMD ["/bin/sh", "-c", ".docker/deploy.sh && /usr/local/bin/start"]
