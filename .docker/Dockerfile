FROM robsontenorio/laravel:3.3 AS base
USER root
RUN groupadd -for -g 999 docker         # If you are trying to build this image locally MacOS, change it from `999` to `0`.
RUN usermod -aG docker appuser
USER appuser
COPY --chown=appuser:appuser . .

FROM base AS local
CMD ["/usr/local/bin/start"]

FROM base AS deploy
RUN composer install --prefer-dist --no-interaction --no-progress --ansi
RUN yarn install && yarn build
RUN chmod a+x .docker/deploy.sh
CMD ["/bin/sh", "-c", ".docker/deploy.sh && /usr/local/bin/start"]
