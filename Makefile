.PHONY: containers-start containers-stop \
	test-parallel test-parallel-and-generate-coverage \
	run-pint run-phpstan run-phpstan-baseline \
	ide-helper-generate composer-dump-autoload \
	config-cache config-clear route-cache \
	optimize optimize-clear \
	after-branch-switch after-branch-switch-fresh-db \
	start-stripe-listener generate-ide-helper generate-sitemap

containers-start:
	./vendor/bin/sail up -d

containers-stop:
	./vendor/bin/sail stop

test-parallel:
	./vendor/bin/sail artisan test --parallel

test-parallel-and-generate-coverage:
	./vendor/bin/sail artisan test --parallel --coverage-html reports/

run-pint:
	./vendor/bin/sail run vendor/bin/pint

run-phpstan:
	./vendor/bin/sail php vendor/bin/phpstan analyse

run-phpstan-baseline:
	./vendor/bin/sail php vendor/bin/phpstan analyse --generate-baseline

ide-helper-generate:
	./vendor/bin/sail artisan ide-helper:generate

composer-dump-autoload:
	./vendor/bin/sail composer dump-autoload

config-cache:
	./vendor/bin/sail artisan config:cache

config-clear:
	./vendor/bin/sail artisan config:clear

route-cache:
	./vendor/bin/sail artisan route:cache

optimize:
	./vendor/bin/sail artisan optimize

optimize-clear:
	./vendor/bin/sail artisan optimize:clear

after-branch-switch:
	./vendor/bin/sail composer install
	./vendor/bin/sail npm ci
	./vendor/bin/sail artisan migrate
	./vendor/bin/sail artisan ide-helper:generate
	./vendor/bin/sail artisan optimize:clear

after-branch-switch-fresh-db:
	./vendor/bin/sail composer install
	./vendor/bin/sail npm ci
	./vendor/bin/sail artisan migrate:fresh --seed
	./vendor/bin/sail artisan ide-helper:generate
	./vendor/bin/sail artisan optimize:clear

start-stripe-listener:
	stripe listen --forward-to localhost:8010/stripe/webhook

generate-ide-helper:
	./vendor/bin/sail artisan ide-helper:models -W
	./vendor/bin/sail run vendor/bin/pint

generate-sitemap:
	./vendor/bin/sail artisan app:generate-sitemaps
