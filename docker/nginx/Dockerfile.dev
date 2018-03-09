FROM nginx:stable-alpine

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

VOLUME /var/www/html

EXPOSE 80
CMD [ "nginx", "-g", "daemon off;" ]
