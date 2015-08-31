errors = $(shell find . -type f -name "*.php" -exec php -l "{}" \;| grep "Errors parsing ";)

test:
	@php ./test/Aplazame.php

syntax.checker:
	@if [ "$(errors)" ];then exit 2;fi

zip:
	@zip -r latest.zip aplazame

push:
	@git push origin HEAD

branch:
	@git checkout master
	@git pull origin master
	@git checkout -b $(branch)
