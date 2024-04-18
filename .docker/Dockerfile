FROM robsontenorio/laravel:3.3 as base
USER root
RUN groupadd -for -g 999 docker         # On MacOS you must change it to `0`.
RUN usermod -aG docker appuser
USER appuser
COPY --chown=appuser:appuser . .

FROM base as local
CMD ["/usr/local/bin/start"]

FROM base as deploy
RUN composer install --prefer-dist --no-interaction --no-progress --ansi
RUN yarn install && yarn build
RUN chmod a+x .docker/deploy.sh
CMD ["/bin/sh", "-c", ".docker/deploy.sh && /usr/local/bin/start"]
