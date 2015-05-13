build:
	rm -r dist
	mkdir dist
	# move everything into dist
	rsync -r --exclude=.git --exclude=dist . dist
	# cleanup
	find dist -name "*.git*" | xargs rm -rf
	rm -rf dist/lib/modules/podlove_web_player/player/podlove-web-player/libs
	rm -r dist/vendor/bin
	rm dist/.travis.yml
	rm dist/wprelease.yml
	rm dist/Rakefile
	rm dist/README.md
	rm dist/CONTRIBUTING.md
	find dist -name "*composer.json" | xargs rm -rf
	find dist -name "*composer.lock" | xargs rm -rf

