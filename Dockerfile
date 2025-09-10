FROM php:8.2-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite

# Configurer Apache pour Cloud Run (port 8080)
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Configurer le document root pour autoriser les .htaccess
RUN echo "<Directory /var/www/html>" > /etc/apache2/conf-available/allow-htaccess.conf
RUN echo "    Options Indexes FollowSymLinks" >> /etc/apache2/conf-available/allow-htaccess.conf
RUN echo "    AllowOverride All" >> /etc/apache2/conf-available/allow-htaccess.conf
RUN echo "    Require all granted" >> /etc/apache2/conf-available/allow-htaccess.conf
RUN echo "</Directory>" >> /etc/apache2/conf-available/allow-htaccess.conf
RUN a2enconf allow-htaccess

# Copier les fichiers de l'application
COPY . /var/www/html/

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 8080
