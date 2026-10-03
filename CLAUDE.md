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
- Cache/queue drivers are Redis. Sessions deliberately use the `file` driver (not Redis, not the
  `sessions` DB table that still exists unused from the default migration) — chosen so the app
  has no session-storage dependency once it moves to the planned Hetzner managed server.
- Sprint & Event Management (`app/Livewire/Teams`, `Sprints`, `SprintEvents`) only ever has one
  kind of logged-in user: the Scrum Master. A `Team` `belongsTo` exactly one owning `User`
  (`teams.user_id`), and a `User` can own several `Team`s (`hasMany`). **Team members are not
  app users** — `TeamMember` is a plain record (`first_name`, `last_name`,
  `default_capacity_percent`) with no login, email, or account of any kind; they exist purely so
  the Scrum Master can plan capacity and track speaking time for people who never touch the app.
  `SprintCapacity` and `DailySpeakingTurn` both belong to a `TeamMember`, not a `User`. Every
  policy (`TeamPolicy`, `SprintPolicy`, `SprintEventPolicy`) collapses to one check —
  `$team->isOwnedBy($user)` — since there is no other role to distinguish; don't reintroduce a
  `manageMembers`/`participate`-style separate ability unless a second real user role actually
  comes back.
- Event types are **not** a fixed enum — each team defines its own Scrum flow via
  `TeamEventType` records (`app/Livewire/Teams/EventTypes.php`, route `teams.event-types`): name,
  icon, default duration/agenda, `is_recurring`, `timing` (`start`/`end`), `track_speaking_time`,
  and `sort_order`. New teams start with **zero** event types — there is no default seed — so a
  freshly created team can't create a sprint until its owner defines at least one. `SprintEvent`
  belongs to a `TeamEventType` (`team_event_type_id`, cascades on delete) instead of carrying a
  `type` string/enum. Creating a sprint (`Sprints/Create.php`) auto-seeds one `SprintEvent` per
  non-recurring (`is_recurring = false`) event type, scheduled on the sprint's start or end date
  per that type's `timing`; recurring types (e.g. a team's "Daily") are instead added on demand
  from the sprint page via `Sprints/Show::addRecurringOccurrence()`, which is idempotent per day.
  Whether the Daily-style speaking-time tracker shows on an event's page is driven by that event
  type's `track_speaking_time` flag, not by any hardcoded "daily" check.

## Testing

Pest 4 (not PHPUnit directly, though PHPUnit is still the underlying runner). Run the suite via
Sail — note the dedicated `testing` MySQL database (already provisioned by Sail's init script)
is used automatically per `phpunit.xml`, not the dev database:

```sh
./vendor/bin/sail test
./vendor/bin/sail test --filter="some test name"
```

All Feature and Unit tests extend `Tests\TestCase` with `RefreshDatabase` applied globally via
`tests/Pest.php`. A shared `createTeamForOwner()` helper (also in `tests/Pest.php`) returns
`[$team, $owner]` and is available in every test file. When testing Livewire action methods that
call `$this->authorize()` and are expected to fail: the **initial mount/render** of a component lets an
`AuthorizationException` propagate as a raw PHP exception (so route-level
`$this->get(...)->assertForbidden()` or wrapping `Livewire::test()` itself in
`expect(fn () => ...)->toThrow(...)` both work there) — but a **subsequent `->call()`** on an
already-mounted component goes through normal exception handling and converts the same exception
into a 403 response instead, so assert it with `->call(...)->assertForbidden()`, not `toThrow()`.
