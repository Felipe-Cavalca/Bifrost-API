#!/bin/sh

set -eu

composer validate --strict --no-check-publish packages/framework/composer.json
composer install --working-dir=packages/framework --no-interaction --no-progress --prefer-dist
composer check --working-dir=packages/framework

for package in cache-redis cache-apcu queue-redis database-pdo log-mongodb storage-local storage-s3; do
    (
        cd "packages/$package"
        composer validate --strict --no-check-publish composer.json
        composer config minimum-stability dev
        composer config repositories.framework path ../framework
        composer update --no-interaction --no-progress --prefer-dist
        composer test
    )
done

for package in database-mysql database-postgresql; do
    (
        cd "packages/$package"
        composer validate --strict --no-check-publish composer.json
        composer config minimum-stability dev
        composer config repositories.framework path ../framework
        composer config repositories.database-pdo path ../database-pdo
        composer update --no-interaction --no-progress --prefer-dist
        composer test
    )
done

(
    cd skeleton
    composer validate --strict --no-check-publish composer.json
    composer config minimum-stability dev
    composer config repositories.framework path ../packages/framework
    composer update --no-interaction --no-progress --prefer-dist
    composer test
)
