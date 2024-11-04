cs_check:
	php vendor/bin/php-cs-fixer fix --dry-run

cs_fix:
	php vendor/bin/php-cs-fixer fix

tests_run:
	php  vendor/bin/phpunit tests