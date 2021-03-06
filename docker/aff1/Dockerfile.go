FROM gitlab.hhdev.net:4567/affninja/docker.images/app:latest
MAINTAINER itstokey <itstokey@gmail.com>

COPY ./docker/aff1/go.nginx.conf /etc/nginx/conf.d/go.nginx.conf
COPY . /var/www/backend.affninja

RUN rm -rf /var/www/backend.affninja/.git && rm -rf /var/www/backend.affninja/tests

RUN mkdir -p /var/www/backend.affninja/storage/app && chown -R www-data:www-data /var/www/backend.affninja && chmod -R 775 /var/www/backend.affninja
RUN mkdir -p /var/www/symlinks && chown -R www-data:www-data /var/www/symlinks && chmod -R 775 /var/www/symlinks

WORKDIR /var/www/backend.affninja

EXPOSE 80 443

CMD ["/usr/bin/supervisord"]