lint:
	php -d memory_limit=512M vendor/bin/phpstan analyse -c phpstan.neon.dist
	./vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php
	./vendor/bin/phpmd bundle text phpmd.xml --exclude '*/bundle/Entity/*'