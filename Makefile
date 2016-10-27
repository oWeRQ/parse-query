all: composer phpunit apigen

composer:
	composer install

phpunit:
	phpunit

apigen:
	yes | apigen generate --config apigen.yaml
