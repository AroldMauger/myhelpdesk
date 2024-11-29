FROM nginx:1.19.8-alpine AS base

# Configure timezone
ARG DOCKER_TIMEZONE=UTC
RUN echo $DOCKER_TIMEZONE > /etc/timezone;

ARG UID=1001
ARG GID=1001

COPY nginx.conf /etc/nginx/nginx.conf
COPY phpfpm.conf etc/nginx/conf.d/phpfpm.conf
COPY default.conf /etc/nginx/conf.d/default.conf

WORKDIR /project

RUN chgrp -R root /var/cache/nginx /var/run /var/log/nginx && \
    chmod -R 770 /var/cache/nginx /var/run /var/log/nginx