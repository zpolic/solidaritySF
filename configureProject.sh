#!/bin/bash

# Run Docker
# Check if docker compose or docker-compose is available
if command -v docker-compose &> /dev/null
then
    # Use docker-compose if available
    docker-compose up -d;
elif docker compose &> /dev/null
then
    # Use docker compose if available
    docker compose up -d;
else
    echo "Error: Neither docker-compose nor docker compose command found"
    exit 1
fi

# Install dependencies
docker exec solidarity-php-container php composer.phar install;

# Create database
docker exec solidarity-php-container php bin/console doctrine:database:create --if-not-exists;

# Create tables
docker exec solidarity-php-container php bin/console doctrine:schema:update --force;