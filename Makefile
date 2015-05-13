build:
	rm -r dist
	mkdir dist
	git clone --recursive --local . dist
	curl -sS https://getcomposer.org/installer | php -- --install-dir=dist
	cd dist && php composer.phar install
	# cleanup
	rm -r dist/**/.git
	rm -r dist/**/.gitignore
	rm -r dist/**/.gitmodules
	rm -r dist/lib/modules/podlove_web_player/player/podlove-web-player/libs
	rm -r dist/vendor/bin
	rm -r dist/Rakefile
	rm -r dist/README.md
	rm -r dist/CONTRIBUTING.md
	rm -r dist/**/composer.*

