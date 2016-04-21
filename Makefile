errors = $(shell find . -type f -name "*.php" -exec php -l "{}" \;| grep "Errors parsing ";)
branch_name = $(shell git symbolic-ref --short HEAD)

test:
	@php ./test/Aplazame.php

syntax.checker:
	@if [ "$(errors)" ];then exit 2;fi

zip:
	@zip -r latest.zip aplazame

push:
	@git push origin HEAD

init.master:
	@git checkout master
	@git pull origin master

release: init.master
	@git checkout release
	@git merge master
	@git push origin release
	@git checkout $(branch_name)

branch: init.master
	@git checkout -b $(branch_name)
