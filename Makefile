.PHONY: local-dev-postgresql
local-dev-postgresql:
	docker-compose --file deploy/dev-postgres/docker-compose.yml up --build -d
	sleep 5
	docker exec -it $$(docker ps | grep php-fpm | awk '{print $$1}') bash -c "./bin/installl.sh"

.PHONY: local-dev-sqlite
local-dev-sqlite:
	@echo "TODO @vojtechmares"
