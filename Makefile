# Kabras Sugar Store Management System - Docker Makefile

.PHONY: help build up down restart logs clean dev prod

# Default target
help: ## Show this help message
	@echo "Kabras Sugar Store Management System - Docker Commands"
	@echo ""
	@echo "Available commands:"
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Development commands
build: ## Build the Docker images
	docker-compose build

up: ## Start all services in development mode
	docker-compose up -d

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

logs: ## Show logs from all services
	docker-compose logs -f

logs-app: ## Show logs from app service only
	docker-compose logs -f app

shell: ## Access the app container shell
	docker-compose exec app bash

db-shell: ## Access the database shell
	docker-compose exec db mysql -u root -p kabras_store

clean: ## Remove all containers, volumes, and images
	docker-compose down -v --rmi all

# Production commands
prod-build: ## Build for production
	docker-compose -f docker-compose.prod.yml build

prod-up: ## Start production services
	docker-compose -f docker-compose.prod.yml up -d

prod-down: ## Stop production services
	docker-compose -f docker-compose.prod.yml down

prod-logs: ## Show production logs
	docker-compose -f docker-compose.prod.yml logs -f

# Database commands
db-backup: ## Create database backup
	docker-compose exec db mysqldump -u root -p kabras_store > backups/db-backup-$(shell date +%Y%m%d-%H%M%S).sql

db-restore: ## Restore database from backup (usage: make db-restore FILE=backup.sql)
	docker-compose exec -T db mysql -u root -p kabras_store < $(FILE)

# Utility commands
install: ## Initial setup - build and start services
	docker-compose build && docker-compose up -d
	@echo "Waiting for database to be ready..."
	@sleep 10
	@echo "Application is running at http://localhost:8080"
	@echo "phpMyAdmin is available at http://localhost:8081"

status: ## Show status of all services
	docker-compose ps

update: ## Pull latest images and restart
	docker-compose pull && docker-compose restart