# Permits API (Laravel)

Small demo API matching gov-tech style CRUD + a domain action (**approve**) + optional **Twilio SMS**. Includes a one-file Blade UI.

## Stack
- PHP 8.2+, Laravel, Eloquent ORM
- PostgreSQL (dev), SQLite for tests
- Optional Twilio SMS on approve
- One-file Blade UI at `/permits`

## Setup
1. Create `.env`, app key, and set DB/Twilio as needed:
    
        cp .env.example .env
        php artisan key:generate

2. Migrate and seed data, then run the dev server:
    
        php artisan migrate --seed
        php artisan serve
        # visit http://127.0.0.1:8000/permits

## Environment keys (example)
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=permits_db
    DB_USERNAME=laravel
    DB_PASSWORD=password

    TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
    TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
    TWILIO_FROM=+1XXXXXXXXXX

## Endpoints
- GET    `/api/permits` — list (paginated). Filters: `?status=approved`, `?q=alex`
- POST   `/api/permits` — create (**201**). Validation errors → **422** with field messages
- GET    `/api/permits/{id}` — show
- PATCH  `/api/permits/{id}` — update
- DELETE `/api/permits/{id}` — delete (**204**)
- POST   `/api/permits/{id}/approve?[notify=1]` — sets `status=approved`; if `notify=1` and `phone_number` present, attempts SMS

## Sample requests
Create:
    
    curl -X POST http://127.0.0.1:8000/api/permits \
      -H "Content-Type: application/json" -H "Accept: application/json" \
      -d '{"number":"PRM-2001","applicant":"Jane Dev","status":"pending"}'

Filter:
    
    curl "http://127.0.0.1:8000/api/permits?status=approved" -H "Accept: application/json"

Approve (+ optional SMS):
    
    curl -X POST "http://127.0.0.1:8000/api/permits/1/approve" -H "Accept: application/json"
    curl -X POST "http://127.0.0.1:8000/api/permits/1/approve?notify=1" -H "Accept: application/json"

## Seeding
Run all seeds:
    
    php artisan db:seed
    # or reset + seed
    php artisan migrate:fresh --seed

The default `PermitSeeder` inserts three example permits (pending/approved/rejected).

## Tests
Feature tests cover valid create (201) and invalid payload (422). Run:
    
    php artisan test

## Notes / Gotchas
- If tests complain about SQLite driver, install it on Linux:
    
        sudo apt install -y php-sqlite3

- If API validation returns a **302** redirect, include:
    
        -H "Accept: application/json"

- Twilio:
  - Use E.164 numbers (`+15551234567`).
  - For trial accounts, verify your destination number or use an SMS-capable Twilio From number.
  - Config is read from `config/services.php` (`services.twilio.*`).
  - Failures are logged as `SMS failed` in `storage/logs/laravel-*.log`.

## Quick architecture (talk track)
Request → **Route** (`routes/api.php`) → **Controller** → **Eloquent Model** (DB) → JSON **Response**.  
Validation uses `$request->validate(...)` and returns **422** with field errors for bad input.  
Twilio is wrapped in `App\Services\TwilioSms` so it can no-op in dev and be faked in tests.

## SQL Server (interview note)
To target MSSQL instead of Postgres: enable Microsoft’s `pdo_sqlsrv`/`sqlsrv` PHP drivers and set `DB_CONNECTION=sqlsrv` in `.env`, then migrate.
