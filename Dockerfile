FROM nginx:alpine
COPY ./html /usr/share/nginx/html
COPY ./nginx.conf /etc/nginx/conf.d/default.conf
RUN rm -f /var/log/nginx/access.log /var/log/nginx/error.log \
    && touch /var/log/nginx/access.log \
    && touch /var/log/nginx/error.log \
    && chmod 666 /var/log/nginx/access.log \
    && chmod 666 /var/log/nginx/error.log
EXPOSE 80
EXPOSE 443
