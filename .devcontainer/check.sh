#!/bin/sh

set -eu

composer validate --strict --no-check-publish packages/framework/composer.json
composer install --working-dir=packages/framework --no-interaction --no-progress --prefer-dist
composer check --working-dir=packages/framework

configure_repository() {
    repository="$1"
    package_name="$(php -r '$composer = json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR); echo $composer["name"];' "../$repository/composer.json")"
    composer config "repositories.$repository" "{\"type\":\"path\",\"url\":\"../$repository\",\"options\":{\"versions\":{\"$package_name\":\"1.0.0\"}}}"
}

test_package() {
    package="$1"
    shift

    (
        cd "packages/$package"
        composer validate --strict --no-check-publish composer.json
        composer config minimum-stability dev
        configure_repository framework

        for repository in "$@"; do
            configure_repository "$repository"
        done

        composer update --no-interaction --no-progress --prefer-dist
        composer test
    )
}

datatype_packages="
datatype-core
datatype-email
datatype-url
datatype-base64
datatype-json
datatype-uuid
datatype-date-time
datatype-cpf
datatype-cnpj
datatype-file-name
datatype-folder-name
datatype-file-path
datatype-folder-path
datatype-storage-key
"

for package in $datatype_packages; do
    test_package "$package" $datatype_packages
done

test_package datatypes $datatype_packages

for package in redis cache-apcu queue-worker database-pdo log-stdout log-file log-mongodb storage-local storage-s3; do
    test_package "$package"
done

for package in cache-redis queue-redis; do
    test_package "$package" redis
done

for package in database-mysql database-postgresql database-sqlite; do
    test_package "$package" database-pdo
done

(
    cd skeleton
    composer validate --strict --no-check-publish composer.json
    composer config minimum-stability dev
    composer config repositories.framework '{"type":"path","url":"../packages/framework","options":{"versions":{"bifrost/framework":"1.0.0"}}}'
    composer update --no-interaction --no-progress --prefer-dist
    composer test
)
