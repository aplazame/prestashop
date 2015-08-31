
test:
	@php ./test/Aplazame.php

syntax.checker:
	@find . -type f -name "*.php" -exec php -l "{}" \;

zip:
	@zip -r latest.zip aplazame
