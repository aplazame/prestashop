errors = $(shell find aplazame -type f -name "*.php" -exec php -l "{}" \;| grep "Errors parsing ";)

syntax.checker:
	@if [ "$(errors)" ];then exit 2;fi

style.req:
	@composer install --no-interaction --quiet --ignore-platform-reqs

style:
	@vendor/bin/php-cs-fixer fix -v

zip:
	@rm -f aplazame.latest.zip
	@zip -r aplazame.latest.zip aplazame
