PHP_CLI="docker-compose exec php"

setup:
	make env-copy
	make down
	make build
	docker-compose up php -d
	make composer c=i
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

up-php:
	docker start spl-php

stop:
	docker-compose stop

bash-php:
	"$(PHP_CLI)" bash

composer:
	"$(PHP_CLI)" composer ${c}

artisan:
	"$(PHP_CLI)" ./vendor/bin/testbench ${c}

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
	make command-workers-benchmark

declare-strict:
	grep -Lr "declare(strict_types=1);" ./src | grep .php

htop-workers:
	htop -t --filter=sparallel-worker-e104f

zombies:
	top -b n1 | grep 'Z'

logs-server:
	docker logs -f spl-server

command-load-server-bin:
	make artisan c=sparallel:load-server-bin

command-server-sleep:
	make artisan c=sparallel:server:sleep

command-server-stats:
	make artisan c=sparallel:server:stats

command-server-stop:
	make artisan c=sparallel:server:stop

command-server-wake-up:
	make artisan c=sparallel:server:wake-up

command-server-workers-reload:
	make artisan c=sparallel:server:workers:reload

command-workers-benchmark:
	make artisan c=sparallel:workers:benchmark
