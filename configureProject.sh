#!/bin/bash

# Check if "docker compose" or "docker-compose" is available
if docker compose &> /dev/null
then
    # Use "docker compose"
    docker compose down;
    docker compose build;
    docker compose up -d;
elif command -v docker-compose &> /dev/null
then
    # Use "docker-compose"
    docker-compose down;
    docker-compose build;
    docker-compose up -d;
else
    echo "Error: Neither docker compose nor docker-compose command found"
    exit 1
fi

# Install dependencies
docker exec solidarity-php-container php composer.phar install;

# Load PAYMENT_PROOF_DIR from .env and .env.local if present by sourcing
for envfile in .env .env.local; do
    [ -f "$envfile" ] && { set -a; source "$envfile"; set +a; }
done

if [ -n "$PAYMENT_PROOF_DIR" ]; then
    docker exec solidarity-php-container chmod 777 "$PAYMENT_PROOF_DIR"
fi

# Create database
docker exec solidarity-php-container php bin/console doctrine:database:create --if-not-exists;

# Dump and create tables
docker exec solidarity-php-container php bin/console doctrine:schema:drop --force;
docker exec solidarity-php-container php bin/console doctrine:schema:update --force;

# Load fixtures
docker exec solidarity-php-container php bin/console doctrine:fixtures:load --group=1 --no-interaction;
docker exec solidarity-php-container php bin/console doctrine:fixtures:load --group=2 --append --no-interaction;
docker exec solidarity-php-container php bin/console doctrine:fixtures:load --group=3 --append --no-interaction;
docker exec solidarity-php-container php bin/console doctrine:fixtures:load --group=4 --append --no-interaction;
docker exec solidarity-php-container php bin/console doctrine:fixtures:load --group=5 --append --no-interaction;
docker exec solidarity-php-container php bin/console doctrine:fixtures:load --group=6 --append --no-interaction;

echo "Done!"
