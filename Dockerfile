FROM chaplean/phpunit
MAINTAINER Tom - Chaplean <tom@chaplean.com>

VOLUME /var/www/symfony
WORKDIR /var/www/symfony/

# Get SSH user key
RUN mkdir /root/.ssh
ADD ./app/config/ssh /root/.ssh
RUN chmod 600 /root/.ssh/id_rsa
RUN chmod 600 /root/.ssh/id_rsa.pub

#COPY . ./
