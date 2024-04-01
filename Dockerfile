# Use the official PHP-Apache image
FROM php:7.3.3-apache

# Set the working directory
#WORKDIR /var/www/html/

WORKDIR /

# Copy the application code into the container
COPY . .

# Install the mysqli extension
RUN docker-php-ext-install mysqli

# Expose port 80
EXPOSE 8008
