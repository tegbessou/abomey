.PHONY: build up down restart shell composer-install composer-update migrate migrate-test unit-test integration-test e2e-test playwright-test cs-check cs-fix phpstan rector-check rector-fix deps-check deptrac quality

## Project
build:
	@docker compose build

up:
	@docker compose up -d

down:
	@docker compose down

restart: down up composer-install

shell:
	@docker compose exec app bash

## Dependencies
composer-install:
	@docker compose exec app composer install

composer-update:
	@docker compose exec app composer update

## Database
migrate:
	@docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

migrate-test:
	@docker compose exec app php bin/console doctrine:database:create --env=test --if-not-exists
	@docker compose exec app php bin/console doctrine:migrations:migrate --env=test --no-interaction

## Tests
unit-test:
	@docker compose exec app php bin/phpunit --testsuite unit

integration-test:
	@docker compose exec app php bin/phpunit --testsuite integration

e2e-test:
	@docker compose exec app php bin/phpunit --testsuite e2e

playwright-test:
	@docker compose exec app php bin/phpunit --testsuite playwright

## Quality
cs-check:
	@docker compose exec app vendor/bin/php-cs-fixer check --diff

cs-fix:
	@docker compose exec app vendor/bin/php-cs-fixer fix

phpstan:
	@docker compose exec app vendor/bin/phpstan analyse -c phpstan.dist.neon

rector-check:
	@docker compose exec app vendor/bin/rector process --dry-run

rector-fix:
	@docker compose exec app vendor/bin/rector process

deps-check:
	@docker compose exec app composer audit

deptrac:
	@docker compose exec app vendor/bin/deptrac analyse

quality: cs-check phpstan rector-check deptrac
