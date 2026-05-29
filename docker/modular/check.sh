#!/bin/sh

set -eu

composer validate --strict --no-check-publish packages/framework/composer.json
composer install --working-dir=packages/framework --no-interaction --no-progress --prefer-dist
composer check --working-dir=packages/framework

for package in datatype-core datatype-email datatype-url datatype-base64 datatype-json datatype-uuid datatype-date-time datatype-cpf datatype-cnpj datatype-file-name datatype-folder-name datatype-file-path datatype-folder-path datatype-storage-key datatypes redis cache-redis cache-apcu queue-redis queue-worker database-pdo log-stdout log-file log-mongodb storage-local storage-s3; do
    (
        cd "packages/$package"
        composer validate --strict --no-check-publish composer.json
        composer config minimum-stability dev
        composer config repositories.framework path ../framework
        composer config repositories.datatype-core path ../datatype-core
        composer config repositories.datatype-email path ../datatype-email
        composer config repositories.datatype-url path ../datatype-url
        composer config repositories.datatype-base64 path ../datatype-base64
        composer config repositories.datatype-json path ../datatype-json
        composer config repositories.datatype-uuid path ../datatype-uuid
        composer config repositories.datatype-date-time path ../datatype-date-time
        composer config repositories.datatype-cpf path ../datatype-cpf
        composer config repositories.datatype-cnpj path ../datatype-cnpj
        composer config repositories.datatype-file-name path ../datatype-file-name
        composer config repositories.datatype-folder-name path ../datatype-folder-name
        composer config repositories.datatype-file-path path ../datatype-file-path
        composer config repositories.datatype-folder-path path ../datatype-folder-path
        composer config repositories.datatype-storage-key path ../datatype-storage-key
        if [ "$package" = "cache-redis" ] || [ "$package" = "queue-redis" ]; then
            composer config repositories.redis path ../redis
        fi
        composer update --no-interaction --no-progress --prefer-dist
        composer test
    )
done

for package in database-mysql database-postgresql database-sqlite; do
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
