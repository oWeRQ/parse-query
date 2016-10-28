all: composer phpunit apigen

composer:
	composer install

phpunit:
	vendor/bin/phpunit

apigen:
	yes | vendor/bin/apigen generate --config apigen.yaml
