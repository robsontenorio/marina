FROM robsontenorio/laravel:3.3 as base
COPY --chown=appuser:appuser . .
USER root
RUN groupadd -for -g 999 docker
RUN usermod -aG docker appuser
RUN mv docker /usr/local/bin/docker
USER appuser

FROM base as local
CMD ["/usr/local/bin/start"]

FROM base as deploy
RUN chmod a+x .docker/deploy.sh
CMD ["/bin/sh", "-c", ".docker/deploy.sh && /usr/local/bin/start"]
