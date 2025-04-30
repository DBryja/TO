#!/bin/bash

DB_PATH=/var/www/database/database.sqlite
SCHEMA=/var/www/database/schema.sql
DB_DIR=/var/www/database

# Make directory structure and set permissions first
mkdir -p $DB_DIR
chown -R www-data:www-data $DB_DIR
chmod -R 775 $DB_DIR

if [ ! -f "$DB_PATH" ]; then
  echo "Tworzenie bazy danych..."
  touch "$DB_PATH"
  chown www-data:www-data "$DB_PATH"
  chmod 664 "$DB_PATH"
  sqlite3 "$DB_PATH" < "$SCHEMA"
else
  echo "Baza danych już istnieje, sprawdzam ewentualne brakujące tabele..."
  # Make sure to fix permissions anyway
  chown www-data:www-data "$DB_PATH"
  chmod 664 "$DB_PATH"
  sqlite3 "$DB_PATH" <<EOF
.read "$SCHEMA"
EOF
fi

# Na końcu odpalenie Apache
exec apache2-foreground