FROM chaplean/phpunit
MAINTAINER Tom - Chaplean <tom@chaplean.com>

# Get SSH user key
RUN mkdir -p /root/.ssh
ADD ./app/config/ssh /root/.ssh
RUN chmod 600 /root/.ssh/*

# Workdir
VOLUME /var/www/symfony
WORKDIR /var/www/symfony/
