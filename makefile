PHP_CLI="docker-compose exec php"

setup:
	make env-copy
	make down
	make build
	docker-compose up php -d
	make load-server-bin
	make stop

env-copy:
	cp -i .env.example .env

build:
	docker-compose build

down:
	docker-compose down

up:
	docker-compose up

up-d:
	docker-compose up -d

watch:
	make up-d
	make server-logs

restart:
	docker-compose restart

stop:
	docker-compose stop

bash-php:
	"$(PHP_CLI)" bash

composer:
	"$(PHP_CLI)" composer ${c}

artisan:
	"$(PHP_CLI)" ./vendor/bin/testbench ${c}

load-server-bin:
	make artisan c=sparallel:server:load

server-logs:
	docker logs -f spl-server

server-stop:
	make artisan c=sparallel:server:stop

server-workers-reload:
	make artisan c=sparallel:server:workers:reload

test:
	"$(PHP_CLI)" ./vendor/bin/phpunit \
		-d memory_limit=512M \
		--colors=auto \
		--testdox \
		--display-incomplete \
		--display-skipped \
		--display-deprecations \
		--display-phpunit-deprecations \
		--display-errors \
		--display-notices \
		--display-warnings \
		tests ${c}

phpstan:
	"$(PHP_CLI)" ./vendor/bin/phpstan analyse \
		--memory-limit=1G

check:
	make phpstan
	make test

declare-strict:
	grep -Lr "declare(strict_types=1);" ./src | grep .php
