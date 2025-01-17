include docker/.env

DOCKER_COMPOSE = docker compose --env-file ./docker/.env
DOCKER_COMPOSE_PHP_EXEC = ${DOCKER_COMPOSE} exec php_swoole_composer

dc_build:
	${DOCKER_COMPOSE} build

dc_up:
	${DOCKER_COMPOSE} up -d --remove-orphans

dc_ps:
	${DOCKER_COMPOSE} ps -a

dc_down:
	${DOCKER_COMPOSE} down --remove-orphans

dc_kill:
	${DOCKER_COMPOSE} kill

php:
	${DOCKER_COMPOSE_PHP_EXEC} /bin/sh

cs_check:
	${DOCKER_COMPOSE_PHP_EXEC} vendor/bin/php-cs-fixer fix --dry-run

cs_fix:
	${DOCKER_COMPOSE_PHP_EXEC} vendor/bin/php-cs-fixer fix

tests_run:
	${DOCKER_COMPOSE_PHP_EXEC}  vendor/bin/phpunit tests

run_app:
	${DOCKER_COMPOSE_PHP_EXEC} php src/app.php

amqp_consume:
	${DOCKER_COMPOSE_PHP_EXEC} php endpoint/amqp_worker.php

composer:
	${DOCKER_COMPOSE_PHP_EXEC} composer install -n