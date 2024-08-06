# Check for docker/podman
DOCKER := $(shell command -v podman 2> /dev/null || command -v docker 2> /dev/null)

# Check for docker-compose/podman-compose
DOCKER_COMPOSE := $(shell command -v podman-compose 2> /dev/null || command -v docker-compose 2> /dev/null)

.PHONY: info
info:
ifeq ($(DOCKER),)
	@echo "Neither docker nor podman is installed."
	exit 1
else
	@echo "Using $(DOCKER)"
endif

ifeq ($(DOCKER_COMPOSE),)
	@echo "Neither docker-compose nor podman-compose is installed."
	exit 1
else
	@echo "Using $(DOCKER_COMPOSE)"
endif

# Start the containers
.PHONY: compose-up
compose-up:
	$(DOCKER_COMPOSE) --file deploy/dev/docker-compose.yml up -d

# Stop the containers
.PHONY: compose-down
compose-down:
	$(DOCKER_COMPOSE) --file deploy/dev/docker-compose.yml down

# Get container ID
.PHONY: $(FPM_ID)
FPM_ID = $(shell $(DOCKER) ps | grep 'quay.io/kissj/php-ubi' | awk '{print $$1}')

.PHONY: composer-install
composer-install:
	$(DOCKER) exec -it -u root $(FPM_ID) sh -c "COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction"

.PHONY: migrate
migrate:
	$(DOCKER) exec -it -u root $(FPM_ID) sh -c "COMPOSER_ALLOW_SUPERUSER=1 composer phinx:migrate --no-interaction"


dev-up: info compose-up composer-install migrate
dev-down: info compose-down

