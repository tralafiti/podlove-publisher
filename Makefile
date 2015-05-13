build:
	rm -r dist
	mkdir dist
	# move everything into dist
	rsync -r --exclude=.git --exclude=dist . dist
	# cleanup
	find dist -name "*.git*" | xargs rm -rf
	rm -rf dist/lib/modules/podlove_web_player/player/podlove-web-player/libs
	rm -rf dist/lib/modules/podlove_web_player/player/podlove-web-player/img/banner-772x250.png
	rm -rf dist/lib/modules/podlove_web_player/player/podlove-web-player/img/banner-1544x500.png
	rm -r dist/vendor/bin
	rm -r dist/vendor/phpunit/php-code-coverage
	rm -r dist/vendor/phpunit/phpunit
	rm -r dist/vendor/phpunit/phpunit-mock-objects
	rm -r dist/vendor/twig/twig/test
	rm -r dist/vendor/guzzle/guzzle/tests
	rm dist/.travis.yml
	rm dist/wprelease.yml
	rm dist/CONTRIBUTING.md
	rm dist/Makefile
	rm dist/Rakefile
	rm dist/README.md
	find dist -name "*composer.json" | xargs rm -rf
	find dist -name "*composer.lock" | xargs rm -rf

