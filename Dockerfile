FROM php:8.2-apache

# Installer les extensions MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copier les fichiers de l'application
COPY . /var/www/html/

# Configurer Apache pour Cloud Run (port 8080)
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
RUN a2enmod rewrite

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080
