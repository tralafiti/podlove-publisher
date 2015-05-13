build:
	rm -rf dist
	mkdir dist
	# move everything into dist
	rsync -r --exclude=.git --exclude=dist . dist
	# cleanup
	find dist -name "*.git*" | xargs rm -rf
	rm -rf dist/lib/modules/podlove_web_player/player/podlove-web-player/libs
	rm -rf dist/lib/modules/podlove_web_player/player/podlove-web-player/img/banner-772x250.png
	rm -rf dist/lib/modules/podlove_web_player/player/podlove-web-player/img/banner-1544x500.png
	rm -rf dist/vendor/bin
	rm -rf dist/vendor/phpunit/php-code-coverage
	rm -rf dist/vendor/phpunit/phpunit
	rm -rf dist/vendor/phpunit/phpunit-mock-objects
	rm -rf dist/vendor/twig/twig/test
	rm -rf dist/vendor/guzzle/guzzle/tests
	rm -f dist/.travis.yml
	rm -f dist/wprelease.yml
	rm -f dist/CONTRIBUTING.md
	rm -f dist/Makefile
	rm -f dist/Rakefile
	rm -f dist/README.md
	find dist -name "*composer.json" | xargs rm -rf
	find dist -name "*composer.lock" | xargs rm -rf
