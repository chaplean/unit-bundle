FROM chaplean/phpunit
MAINTAINER Tom - Chaplean <tom@chaplean.com>

# Update the default apache site with the config of the project.
ADD app/config/apache-vhost.conf /etc/apache2/sites-enabled/000-default.conf

# Set VirtualHost
ENV VIRTUAL_HOST unit-bundle.chaplean.fr

VOLUME /var/www/symfony
WORKDIR /var/www/symfony/

# Get SSH user key
RUN mkdir /root/.ssh
ADD ./app/config/ssh /root/.ssh
RUN chmod 600 /root/.ssh/id_rsa
RUN chmod 600 /root/.ssh/id_rsa.pub

#COPY . ./
