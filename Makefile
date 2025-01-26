DIR_TEST=tests
DIR_SOURCE=www
PHP_VERSION=8.3

url_check=code=`curl --write-out '%{http_code}' --silent --output /dev/null "http://inflacni-kalkulacka.test$1"` && \
	echo "$1 => Exp: $2, Was: $${code}" && \
	test $${code} -eq $2

# php$(PHP_VERSION)-json
install-linux-packages:
	apt-get install -y \
		php$(PHP_VERSION)-dom \
		php$(PHP_VERSION)-cli \
		php$(PHP_VERSION)-mbstring \
		php$(PHP_VERSION)-xml \
		php$(PHP_VERSION)-pcov \
		php$(PHP_VERSION)-xdebug

install-dependencies:
	composer self-update
	composer install
	composer dump-autoload

install-pre-commit:
	python -m pip install -U pip
	python -m pip install pre-commit
	pre-commit install

update:
	composer update

check-all: test check-code-sniffer check-web

test:
	./vendor/bin/phpunit $(DIR_TEST)

#
coverage:
	php \
		-d pcov.enabled=1 \
		-d pcov.directory=www \
		./vendor/bin/phpunit \
		--coverage-clover coverage.xml \
		--coverage-html coverage.html \
		$(DIR_TEST)

check-code-sniffer:
	./vendor/bin/phpcbf \
		--standard=PSR12 \
		-p -s \
		$(DIR_SOURCE)/ $(DIR_TEST)/; \
	./vendor/bin/phpcs \
		--standard=PSR12 \
		-p -s \
		$(DIR_SOURCE)/ $(DIR_TEST)/

check-web:
	$(call url_check,/,200) && \
	$(call url_check,/?year=2040&value=10000,400) && \
	$(call url_check,/?year=2020&value=10000,200) && \
	$(call url_check,/?year=2020&value=10+000,200) && \
	$(call url_check,/?year=2020&value=10+000.24,200) && \
	$(call url_check,/?target=2020&values=1994%3B10000%0D%0A1995%3B10000,200) && \
	$(call url_check,/?target=2020&values=1994%3B%0D%0A1995%3B10000,400) && \
	$(call url_check,/?format=json,200) && \
	$(call url_check,/?year=2040&value=10000&format=json,400) && \
	$(call url_check,/?year=2020&value=10000&format=json,200) && \
	$(call url_check,/?target=2020&values=1994%3B10000%0D%0A1995%3B10000&format=json,200) && \
	$(call url_check,/?target=2020&values=1994%3B%0D%0A1995%3B10000&format=json,400)
