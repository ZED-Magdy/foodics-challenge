# Foodics task

## make sure to have the following installed

- docker
- docker-compose
- composer

## To run the project

- Clone the project
- Run the following commands in the project directory

```shell
composer install
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:gen
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan horizon
./vendor/bin/sail artisan scribe:gen
./vendor/bin/sail artisan test
```

- Visit `http://localhost:8000/docs` or `http://localhost:8000/swagger` in your browser
