#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log

	if [ "$APP_ENV" != 'prod' ]; then
		rm -f .env.local.php
		composer install --prefer-dist --no-progress --no-interaction
	fi

	if [ "$APP_ENV" != 'test' ]; then
		jwt_passhrase=$(grep ''^JWT_PASSPHRASE='' .env | cut -f 2 -d ''='' | sed "s/%kernel.project_dir%/./g")
		jwt_secret_key=$(grep ''^JWT_SECRET_KEY='' .env | cut -f 2 -d ''='' | sed "s/%kernel.project_dir%/./g")
		jwt_public_key=$(grep ''^JWT_PUBLIC_KEY='' .env | cut -f 2 -d ''='' | sed "s/%kernel.project_dir%/./g")

		echo "$jwt_passhrase" | openssl genpkey -out $jwt_secret_key -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
		echo "$jwt_passhrase" | openssl pkey -in $jwt_secret_key -passin stdin -out $jwt_public_key -pubout

		chown -R "$(whoami)":"$(whoami)" $jwt_secret_key $jwt_public_key
		chmod -R 644 $jwt_secret_key $jwt_public_key
	fi

	if grep -q ^DATABASE_URL= .env; then
		echo "Waiting for db to be ready..."
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(bin/console dbal:run-sql "SELECT 1" 2>&1); do
			if [ $? -eq 255 ]; then
				# If the Doctrine command exits with 255, an unrecoverable error occurred
				ATTEMPTS_LEFT_TO_REACH_DATABASE=0
				break
			fi
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "Still waiting for db to be ready... Or maybe the db is not reachable. $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left"
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo "The database is not up or not reachable:"
			echo "$DATABASE_ERROR"
			exit 1
		else
			echo "The db is now ready and reachable"
		fi

		if ls -A migrations/*.php >/dev/null 2>&1; then
			bin/console doctrine:migrations:migrate --no-interaction
		fi
	fi

	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var
fi

exec docker-php-entrypoint "$@"
