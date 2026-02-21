.PHONY: help dev-setup docker-up composer-install docker-down shell install lint lint-check stan test mutation all clean

container-command := docker compose exec interest-account-lib

help:
	@echo "Available commands:"
	@echo "  dev-setup     - Start Docker containers"
	@echo "  install       - Install Composer dependencies"
	@echo "  lint          - Fix code style issues"
	@echo "  lint-check    - Check code style without fixing"
	@echo "  stan          - Run PHPStan static analysis"
	@echo "  test          - Run PHPUnit tests"
	@echo "  mutation      - Run Infection mutation testing"
	@echo "  check           - Run all quality checks"
	@echo "  clean         - Install production dependencies only"
	@echo "  shell         - Open bash shell in container"
	@echo "  docker-up     - Start Docker containers"
	@echo "  docker-down   - Stop Docker containers"

dev-setup: clean docker-up composer-install

docker-up:
	@docker compose up -d --build

docker-down:
	@docker compose down --volumes --remove-orphans

composer-install:
	$(container-command) composer install

shell:
	$(container-command) bash

install:
	$(container-command) composer install

lint:
	$(container-command) composer lint

lint-check:
	$(container-command) composer lint:check

stan:
	$(container-command) composer stan

test:
	$(container-command) composer test

mutation:
	$(container-command) composer mutation

check:
	$(container-command) composer check

clean:
	rm -rf temp
	rm -rf vendor

