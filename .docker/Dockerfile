FROM robsontenorio/laravel:3.2 as base
COPY --chown=appuser:appuser . .
COPY docker /usr/local/bin/docker


FROM base as local
USER root
RUN usermod --append --groups 0 appuser # TODO: gid must be dynamic (on my Mac is `0`, but Ubuntu Server is `999`)
USER appuser
CMD ["/usr/local/bin/start"]

FROM base as deploy
RUN chmod a+x .docker/deploy.sh
CMD ["/bin/sh", "-c", ".docker/deploy.sh && /usr/local/bin/start"]
