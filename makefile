PHP_CLI="docker-compose run -it --rm --user $$(id -u):$$(id -g) php"
REDIS_CLI="docker-compose exec redis redis-cli"

build:
	docker-compose build

down:
	docker-compose down

bash:
	"$(PHP_CLI)" bash

redis:
	"$(REDIS_CLI)"

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

declare-strict:
	grep -Lr "declare(strict_types=1);" ./src | grep .php
