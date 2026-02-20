# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

KISSJ (Keep It Simple Stupid for Jamborees) is a Scout registration system for national and international jamborees. Built with PHP 8.3+, Slim 4 framework, LeanMapper ORM, and PostgreSQL.

## Git Workflow

Trunk-based with a staging branch.
Feature branches are rebased into `staging` for testing, then rebased into `master` for release.
PRs target `staging` unless explicitly told otherwise.
Git commit messages should be concise and starts with non-capitalized past tense verb (e.g., "added", "fixed", "refactor"), followed by a brief description of the change.

## Commands

All commands run inside the Docker container:

```bash
# Full quality suite (PHPStan + CS Fixer + PHPUnit)
docker exec -u 1000 -it kissj-app-php-fpm-1 composer test

# Individual tools
docker exec -u 1000 -it kissj-app-php-fpm-1 composer stan    # PHPStan (max level)
docker exec -u 1000 -it kissj-app-php-fpm-1 composer cs      # PHP-CS-Fixer (PSR-12)
docker exec -u 1000 -it kissj-app-php-fpm-1 composer unit    # PHPUnit

# Database migrations
docker exec -u 1000 -it kissj-app-php-fpm-1 composer phinx:migrate
docker exec -u 1000 -it kissj-app-php-fpm-1 composer phinx:rollback

# Local dev environment
make dev-up     # Start containers + install + migrate
make dev-down   # Stop containers
```

## Architecture

**Entry point:** `public/index.php` → `ApplicationGetter::getApp()` bootstraps DI (PHP-DI), middleware, and routes.

**Request flow:** Caddy → PHP-FPM → Slim middleware stack → Route → Controller → Service → Repository → LeanMapper → PostgreSQL

**Key directories:**
- `src/Application/` — Bootstrap, routing (`Route.php`), middleware registration (`Middleware.php`), Phinx migrations
- `src/Settings/Settings.php` — DI container definitions (all service wiring)
- `src/Participant/` — Core domain: registration models, controllers, services
- `src/Event/EventType/` — Event variant configurations (multiple event types with different field rules)
- `src/Templates/` — Twig templates + translation YAML files (`cs.yaml`, `en.yaml`, `sk.yaml`)

**Single Table Inheritance for Participants:** All participant types (`PatrolLeader`, `PatrolParticipant`, `TroopLeader`, `TroopParticipant`, `Ist`, `Guest`) map to one `participant` table, discriminated by a `role` column via `ParticipantRole` enum. The `Orm/Mapper.php` handles this routing.

**Content Arbiters:** Each participant role has an `AbstractContentArbiter` subclass that determines which form fields are visible/editable for that role and event type combination.

**Authorization:** Middleware-based — routes are protected by stacked middleware classes in `src/Middleware/` (e.g., `AdminsOnlyMiddleware`, `PatrolLeadersOnlyMiddleware`, `OpenStatusOnlyMiddleware`).

**Authentication:** Passwordless — login via email token or Skautis OAuth. No traditional passwords.

## Fragile Areas

Do not modify without extra caution:
- **Authentication flow** — passwordless login, Skautis OAuth integration
- **`Orm/Mapper.php`** — single table inheritance routing; subtle changes break entity mapping

## Code Style

- Use imports, not FQN
- Use static typing instead of PHPDoc
- Do not use overly expressive comments
- Use `LogicException` for impossible/unexpected states that should never occur
- In tests, use static assertion methods (e.g., `self::assertEquals()`)
- PHPStan level 10 with a baseline (`phpstan-baseline.neon`) for pre-existing issues
- PSR-12 style enforced by PHP-CS-Fixer

## Testing

Always write tests for new features and bugfixes.

## Translations

When adding user-facing strings, add translations to all three YAML files: `cs.yaml`, `en.yaml`, `sk.yaml`.
Translations must be ordered alphabetically by key for consistency.

## Database Migrations

Always create a Phinx migration for schema changes — no manual SQL.

## Configuration

- `.env` is for local development only; keep `.env.example` in sync with any new variables
- Production uses environment variables loaded from Vault — do not modify production config
- Never commit `.env` or secrets

## Off-Limits

- `deploy/` directory (Helm charts, deployment config) — do not touch

## Longevity

Codebase is actively maintained. Do not change code recklessly; ensure changes are actually needed and bring value in performance or usability.
