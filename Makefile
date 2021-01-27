.PHONY: php-from
php-from:
	docker build --pull -t interfaces/from:php -f languages/php/from.Dockerfile languages/php

.PHONY: php-to
php-to:
	docker build --pull -t interfaces/to:php -f languages/php/to.Dockerfile languages/php

.PHONY: test
test: php-from php-to
	cat tests/php/interface.php | docker run -i --rm interfaces/from:php | docker run -i --rm interfaces/to:php
