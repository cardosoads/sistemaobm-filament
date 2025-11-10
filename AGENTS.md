# Repository Guidelines

## Project Structure & Module Organization
This is a Laravel 12 + Filament 4 monolith. Domain logic, Filament resources, and Livewire widgets live under `app/` (`app/Filament`, `app/Http`, `app/Livewire`, `app/Models`). Shared config stays in `config/`, while migrations, seeders, and factories are under `database/`. Front-end assets (Tailwind + Vite) live in `resources/js` and `resources/css`, with Blade views in `resources/views`. Public bundles compile to `public/build`. End-to-end Playwright specs currently sit in `tests/`, alongside the default PHP test namespace.

## Build, Test, and Development Commands
- `composer install && npm install` — sync PHP and Node dependencies.
- `php artisan key:generate` and `php artisan migrate --seed` — bootstrap `.env` and database.
- `composer dev` — launches the Laravel server, queue listener, and Vite dev server concurrently.
- `npm run build` — produces production assets via Vite.
- `composer test` — clears the config cache and runs the Pest-powered Laravel test suite.
- `npm run test:e2e` (or `:ui`, `:headed`) — runs Playwright specs in `tests/*.spec.js`.

## Coding Style & Naming Conventions
Follow PSR-12 and Laravel defaults; run `./vendor/bin/pint` before pushing. Namespace classes to match folder paths (`App\Filament\Resources\UserResource`). Blade/Livewire view files use kebab-case (for example, `resources/views/users/index.blade.php`). Stick to snake_case migration tables, PascalCase PHP classes, and camelCase JS variables. Keep services small and cohesive under `app/Services`.

## Testing Guidelines
Keep Pest feature tests under `tests/Feature/*Test.php` and unit specs under `tests/Unit/*Test.php` (create directories as needed). Mirror the class under test in the namespace and keep one behavior focus per test. Use database transactions (`RefreshDatabase`) for Filament form features. For browser-critical flows, add Playwright specs (`tests/*.spec.js`) and reference stable data seeded via factories. Block merges unless `composer test` and `npm run test:e2e` pass locally.

## Commit & Pull Request Guidelines
Use present-tense, descriptive commits (`fix: enforce role guards on Filament pages`) instead of `wip`. Reference issues in the body (`Relates #123`) and summarize behavior changes. PRs should include: purpose, implementation notes (migrations, env flags, queues), screenshots or GIFs for UI diffs, and explicit verification steps (tests run, seed data used). Request review from a teammate familiar with the touched module and keep PRs under ~400 lines when possible.

## Security & Configuration Tips
Never commit `.env` or real secrets; rely on `.env.example` to illustrate new keys. Queue workers (`composer dev` or `php artisan queue:listen`) must run when testing background jobs. Always run `php artisan config:clear` after adjusting config or permissions to avoid cached settings.
