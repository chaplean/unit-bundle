FROM chaplean/php:7.1
MAINTAINER Tom - Chaplean <tom@chaplean.coop>

# Get SSH user key
RUN mkdir -p /root/.ssh
ADD ./private/ssh /root/.ssh
RUN chmod 600 /root/.ssh/*

# Workdir
VOLUME /var/www/symfony
WORKDIR /var/www/symfony/
