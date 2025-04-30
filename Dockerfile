FROM php:8.2-apache

# Zainstaluj SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite

# Włącz mod_rewrite
RUN a2enmod rewrite

# Skopiuj pliki projektu
COPY ./public /var/www/html
COPY ./database /var/www/database
COPY ./scripts /var/www/scripts

# Ustaw uprawnienia do skryptu
RUN chmod +x /var/www/scripts/create_db.sh

# Ustaw katalog roboczy
WORKDIR /var/www/html

# Uruchom skrypt podczas startu kontenera
CMD ["/bin/bash", "/var/www/scripts/create_db.sh", "&&", "apache2-foreground"]
