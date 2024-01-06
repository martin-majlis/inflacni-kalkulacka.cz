DIR_TEST=tests
DIR_SOURCE=www

url_check=code=`curl --write-out '%{http_code}' --silent --output /dev/null "http://inflacni-kalkulacka.test$1"` && \
	echo "$1 => Exp: $2, Was: $${code}" && \
	test $${code} -eq $2

install-linux-packages:
	aptitude install \
		php-xmlwriter \
		php-dom \
		php-cli \
		php-json \
		php-mbstring \
		php-xml \
		php-pcov \
		php-xdebug

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
	$(call url_check,/?year=2030&value=10000,400) && \
	$(call url_check,/?year=2020&value=10000,200) && \
	$(call url_check,/?format=json,200) && \
	$(call url_check,/?year=2030&value=10000&format=json,400) && \
	$(call url_check,/?year=2020&value=10000&format=json,200)
