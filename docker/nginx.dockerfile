FROM nginx:stable

WORKDIR /var/www/html

RUN rm /etc/nginx/conf.d/default.conf
ADD /docker/nginx/nginx.conf /etc/nginx/nginx.conf
ADD /docker/nginx/default.conf /etc/nginx/conf.d/default.conf