.PHONY: up down shell install test migrate seed fresh logs

up:
	docker compose up -d

down:
	docker compose down

shell:
	docker compose exec app bash

install:
	docker compose exec app composer run setup

test:
	docker compose exec app php artisan test --compact

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed --class=DevSeeder

fresh:
	docker compose exec app php artisan migrate:fresh --force
	docker compose exec app php artisan db:seed --class=DevSeeder

logs:
	docker compose logs -f app
