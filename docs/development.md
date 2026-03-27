# Development Environment

Two options: local PHP or Docker Compose.

## Option A — Local PHP

Requires: PHP 8.3+, Composer 2, Node.js 20+, SQLite.

```bash
git clone https://github.com/onepointhub/onepointhub.git && cd onepointhub
composer run setup
composer run dev
```

Visit http://localhost:8000.

## Option B — Docker Compose

Requires: Docker Desktop 4.x+ (or Docker Engine + Compose plugin).

```bash
git clone https://github.com/onepointhub/onepointhub.git && cd onepointhub
cp .env.docker .env
docker compose up -d
make install
```

Visit http://localhost:8000. Mail UI at http://localhost:8025 (Mailpit).

### Useful commands

| Command        | Description                                        |
|----------------|----------------------------------------------------|
| `make up`      | Start all containers                               |
| `make down`    | Stop all containers                                |
| `make shell`   | Open a bash shell in the app container             |
| `make install` | Install dependencies, run migrations, build assets |
| `make test`    | Run Pest test suite                                |
| `make fresh`   | Drop all tables, re-migrate, re-seed               |
| `make seed`    | Run the DevSeeder (demo data)                      |
| `make logs`    | Tail app container logs                            |

### Services

| Service        | URL                   |
|----------------|-----------------------|
| App            | http://localhost:8000 |
| MySQL          | localhost:3306        |
| Redis          | localhost:6379        |
| Mailpit (SMTP) | localhost:1025        |
| Mailpit (UI)   | http://localhost:8025 |
