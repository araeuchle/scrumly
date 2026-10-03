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

- **Navigation is built around a "current team" stored on the user**, not just the `{team}` route
  parameter. `users.current_team_id` (nullable FK to `teams`, `nullOnDelete`) tracks which team is
  active; `User::resolveCurrentTeam()` is the single source of truth for reading it — it returns
  the stored team only if it still belongs to the user (checked via `isOwnedBy()`, since a plain
  `belongsTo` relation would happily resolve a team owned by someone else if the column were ever
  wrong), otherwise falls back to the user's first team (`oldest('id')`) and self-heals the column
  to match. The sidebar (`components/layouts/app.blade.php`) calls this once per request to decide
  whether to render the team-scoped nav group (Mitglieder/Sprints/Event-Typen/Impediments) at all,
  and embeds `<livewire:teams.switcher />` — a small persistent child component
  (`App\Livewire\Teams\Switcher`) with a `<flux:select>` bound to `currentTeamId` — so switching
  teams is one request that updates `current_team_id` and redirects, not a page link. Every place
  a team becomes "current" (creating one in `Teams\Index::createTeam()`, clicking a team card via
  `Teams\Index::selectTeam()`, or the switcher's `updatedCurrentTeamId()`) must write
  `current_team_id` through a query scoped to `$user->teams()` (e.g. `->findOrFail($teamId)`)
  rather than loading the team by raw ID first — that scoping is what stops a user from switching
  into a team they don't own, there's no separate authorization check layered on top of it.
- **Validation in Livewire components always goes through a `Livewire\Form` object**, never
  `$this->validate()` inline on the component. Every form lives under `app/Livewire/Forms/`
  (e.g. `TeamEventTypeForm`, `ImpedimentForm`), is exposed as `public FooForm $form` on the
  component, bound in Blade as `wire:model="form.fieldName"`, and submitted with
  `$this->form->validate()`. Note classic Laravel `FormRequest` classes do *not* work here —
  Livewire action methods aren't resolved through the controller pipeline, so the container can't
  inject one the way it does for a controller method. Validation errors land in the `form.*`
  error-bag namespace (`assertHasErrors('form.title')` in tests, `@error('form.title')` in
  Blade), not under the bare field name. Cross-aggregate/business-rule checks that need context
  the Form doesn't have (e.g. "this sprint must belong to this team") stay out of the Form's
  `rules()` and are checked explicitly in the component after `$this->form->validate()` passes,
  using `$this->addError('form.field', '...')` — see `Impediments/Index::save()`'s `sprintId`
  check for the pattern.
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
  per that type's `timing`; any event type (recurring or not) can also be added on demand from the
  sprint page via a dialog calling `Sprints/Show::addEventOccurrence()`, which is idempotent per
  day. Whether the Daily-style speaking-time tracker shows on an event's page is driven by that
  event type's `track_speaking_time` flag, not by any hardcoded "daily" check. `SprintEvent` also
  has a free-text `notes` column (distinct from `agenda`) for what actually happened, and
  `remainingSeconds()` freezes its calculation at `ended_at` once an event is completed — it must
  never keep computing against `now()` for a finished event, or the UI reports a growing overtime
  forever.
- **`wire:model.blur` does not fire its request in this Livewire 4 + Flux setup** — confirmed by
  direct testing: the browser-side state updates, a real `blur` event fires, but no network
  request is ever sent (verified via the container's access log, not just dev tools). Use
  `wire:model.live.debounce.750ms` (or similar) instead for anything that needs to persist as the
  user types without an explicit save button, as done in `SprintEvents/Show`'s agenda and notes
  fields. Don't reach for `.blur` again without re-verifying it against a real browser first.
- Impediment tracking (`app/Livewire/Impediments/Index.php`, route `teams.impediments`) belongs
  directly to a `Team` (`impediments.team_id`, cascades on delete), with an **optional**
  `sprint_id` (`nullOnDelete`) — an impediment doesn't have to be raised inside a sprint, and
  deleting the sprint it was raised in must not delete the impediment. Status is a three-stage
  lifecycle on `ImpedimentStatus` (`open` → `escalated` → `resolved`, matching the product's
  "erfassen, eskalieren, auflösen" pitch), not a boolean flag; `escalate()`/`resolve()`/`reopen()`
  on the model set the matching `escalated_at`/`resolved_at` timestamps, and `reopen()` clears
  both so an impediment can go back to `open` from either later state. Priority
  (`ImpedimentPriority`: low/medium/high/critical) is a separate enum, independent of status.
  Reporter and owner (`reported_by`, `owner`) are deliberately plain free-text columns, not
  `TeamMember` references — unlike capacity/speaking-time tracking, nothing here requires a
  structured link to a roster entry. `ImpedimentPolicy` needs a `viewAny(User, Team)` ability
  (the index page is per-team) in addition to the usual `view`/`create`/`update`/`delete`; because
  `create`/`viewAny` only have a `Team` in hand (no `Impediment` instance yet), they must be
  authorized as `$this->authorize('create', [Impediment::class, $team])`, not
  `$this->authorize('create', $team)` — passing the bare `Team` model resolves `TeamPolicy`
  instead of `ImpedimentPolicy` and silently checks the wrong thing.

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

## Static analysis

Larastan (`phpstan.neon`) runs at **level 9** via `make run-phpstan`. Two non-default parameters
matter for this codebase: `checkModelProperties: true` makes Larastan trust the DB schema for
column nullability (and nullable `BelongsTo`/`HasOne` magic-property access must be fixed with a
`@property-read` docblock on the model when the foreign key is actually `NOT NULL` — Larastan
otherwise always treats relation properties as nullable, since it can't prove a relation is loaded).
`parseModelCastsMethod: true` is required because models here declare casts via the Laravel 11+
`protected function casts(): array` method rather than the legacy `protected $casts` array —
without this flag Larastan ignores the method entirely and infers raw column types (e.g. a
`datetime`-cast column shows up as `Carbon|string`, enum casts don't narrow at all), producing
spurious errors. At level 9, `checkExplicitMixed` is on, so anything the framework itself types as
`@return mixed` (e.g. `Builder::max()`, `Password::reset()`) can no longer be blindly cast — narrow
it first with `is_numeric()`/`is_string()`/etc. before casting or comparing.
