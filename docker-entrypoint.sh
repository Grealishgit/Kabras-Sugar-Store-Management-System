#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database connection..."
until php -r "new PDO('mysql:host=db;dbname=kabras_store;charset=utf8mb4', 'root', 'Hunter42.');" 2>/dev/null; do
  echo "Database not ready, waiting..."
  sleep 2
done

echo "Database is ready!"

# Run database migrations or setup if needed
# php setup.php

# Start Apache
echo "Starting Apache..."
apache2-foreground