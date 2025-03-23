# Only for getting the docker cli
FROM docker:28 AS docker-cli

# Base image
FROM robsontenorio/laravel:3.5 AS base
COPY --from=docker-cli /usr/local/bin/docker /usr/local/bin/docker
COPY --chown=appuser:appuser . .

# For local development
FROM base AS local
CMD ["/usr/local/bin/start"]

# Used to build the final image for distribution
FROM base AS deploy
RUN composer install --prefer-dist --no-interaction --no-progress --ansi
RUN yarn install && yarn build
RUN chmod a+x .docker/deploy.sh
CMD ["/bin/sh", "-c", ".docker/deploy.sh && /usr/local/bin/start"]
