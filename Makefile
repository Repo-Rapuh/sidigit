COMPOSE ?= podman compose
LOCAL_FILE ?= docker-compose.local.yml
PROD_FILE ?= docker-compose.prod.yml
SERVICE ?= app
ARTISAN_CMD ?=

.PHONY: help up down stop build logs shell artisan composer-install migrate seed prod-up prod-down prod-build prod-logs

help: ## Show available targets
	@grep -E '^[a-zA-Z0-9_-]+:.*##' Makefile | sort | awk 'BEGIN {FS = ":.*## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start the local stack (build if needed)
	$(COMPOSE) -f $(LOCAL_FILE) up -d --build

up-recreate: ## Force recreate the local stack
	$(COMPOSE) -f $(LOCAL_FILE) up -d --build --force-recreate

restart: ## Restart all local services
	$(COMPOSE) -f $(LOCAL_FILE) restart

down: ## Stop and remove the local stack
	$(COMPOSE) -f $(LOCAL_FILE) down

stop: ## Stop the local containers without removing them
	$(COMPOSE) -f $(LOCAL_FILE) stop

build: ## Build local images
	$(COMPOSE) -f $(LOCAL_FILE) build

logs: ## Tail logs for a service (make logs SERVICE=queue)
	$(COMPOSE) -f $(LOCAL_FILE) logs -f $(SERVICE)

shell: ## Open a shell inside the app container
	$(COMPOSE) -f $(LOCAL_FILE) exec $(SERVICE) sh

artisan: ## Run an artisan command (make artisan ARTISAN_CMD='migrate --force')
	$(COMPOSE) -f $(LOCAL_FILE) exec app php artisan $(ARTISAN_CMD)

composer-install: ## Install PHP dependencies in the app container
	$(COMPOSE) -f $(LOCAL_FILE) exec app composer install

migrate: ## Run database migrations locally
	$(COMPOSE) -f $(LOCAL_FILE) exec app php artisan migrate

seed: ## Seed the database locally
	$(COMPOSE) -f $(LOCAL_FILE) exec app php artisan db:seed

prod-up: ## Start the production stack (detached)
	$(COMPOSE) -f $(PROD_FILE) up -d --build

prod-down: ## Stop and remove the production stack
	$(COMPOSE) -f $(PROD_FILE) down

prod-build: ## Build production images only
	$(COMPOSE) -f $(PROD_FILE) build

prod-logs: ## Tail logs from the production app service
	$(COMPOSE) -f $(PROD_FILE) logs -f app
