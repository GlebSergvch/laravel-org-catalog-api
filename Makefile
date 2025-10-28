up:
	docker-compose up -d

stop:
	docker-compose stop

install-local:
	cp .env.local .env
	docker-compose build --no-cache
	docker-compose up -d
	docker-compose exec org-catalog-app composer install
# 	sudo chown -R $USER:$USER ./
	sudo chmod -R 777 ./storage/logs
	sudo chmod -R 777 ./storage/framework
	docker-compose exec org-catalog-app php artisan key:generate
	docker-compose exec org-catalog-app php artisan migrate:fresh --seed

migrate:
	docker-compose exec org-catalog-app php artisan migrate

route-list:
	docker-compose exec org-catalog-app php artisan route:list

swagger-generate:
	docker-compose exec org-catalog-app php artisan l5-swagger:generate
	sudo chmod -R 777 ./storage/api-docs
