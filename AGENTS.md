# Scrumly

Laravel 13 + Livewire 4 + Flux Pro application. Scrumly helps Scrum Masters offload the
organizational overhead of their role (sprint events, retrospectives, impediment tracking,
team metrics).

## Environment: Docker / Sail only

This project's host machine has no PHP, Composer, or Node installed on purpose. **Never**
install PHP, PHP extensions, or global Composer/NPM packages on the host. All tooling runs
inside Docker via Laravel Sail.

Run every PHP/Composer/Artisan/NPM command through Sail:

```sh
./vendor/bin/sail up -d        # start containers
./vendor/bin/sail artisan ...  # artisan commands
./vendor/bin/sail composer ... # composer commands
./vendor/bin/sail npm ...      # npm commands
./vendor/bin/sail test         # run the test suite
```

If `vendor/` doesn't exist yet (fresh clone, no Sail binary available), bootstrap dependencies
using the Dockerized Composer image instead of host PHP:

```sh
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install
```

## Ports

Sail's default ports (80, 3306, 5173, 6379) may collide with other local projects. This
project is configured in `.env` to use: app on `8010`, MySQL on `3309`, Redis on `6389`,
Vite on `5183`. Adjust there if needed, not in `compose.yaml`.

## Stack notes

- Flux Pro is a licensed package from a private Composer repository (`composer.fluxui.dev`).
  Its credentials live in the project-local `auth.json` (gitignored, never commit it).
- Auth (login, registration, password reset) is built with class-based Livewire components
  under `app/Livewire/Auth`, not a starter kit — there's no Breeze/Jetstream/Fortify installed.
- UI locale is German (`APP_LOCALE=de`); `lang/de/*` holds the translations.
- Session/cache/queue drivers are Redis.
