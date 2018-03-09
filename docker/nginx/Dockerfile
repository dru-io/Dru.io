FROM nginx:stable-alpine

ADD . /var/www/html
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80
CMD [ "nginx", "-g", "daemon off;" ]
