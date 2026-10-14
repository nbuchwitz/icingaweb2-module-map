.PHONY: lint phpcs

lint:
	vendor/bin/phplint -n --exclude={^vendor/.*} application library configuration.php run.php
phpcs:
	vendor/bin/phpcs
