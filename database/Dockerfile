FROM php:7.3.3-apache
WORKDIR /var/www/html/
COPY . .
RUN docker-php-ext-install mysqli
EXPOSE 8008
