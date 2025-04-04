#!/bin/bash

# Run Docker
docker compose up -d;

# Install dependencies
docker exec solidarity-php-container php composer.phar install;

# Create database
docker exec solidarity-php-container php bin/console doctrine:database:create --if-not-exists;

# Create tables
docker exec solidarity-php-container php bin/console doctrine:schema:update --force;