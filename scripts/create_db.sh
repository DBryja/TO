#!/bin/bash

DB_PATH=/var/www/database/database.sqlite
SCHEMA=/var/www/database/schema.sql

if [ ! -f "$DB_PATH" ]; then
  echo "Tworzenie bazy danych..."
  sqlite3 "$DB_PATH" < "$SCHEMA"
else
  echo "Baza danych już istnieje, sprawdzam ewentualne brakujące tabele..."
  sqlite3 "$DB_PATH" <<EOF
.read "$SCHEMA"
EOF
fi

# Na końcu odpalenie Apache
exec apache2-foreground
