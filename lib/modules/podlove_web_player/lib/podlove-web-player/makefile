build:
	mkdir lib
	mkdir lib/podlove-web-player
	wget https://github.com/podlove/podlove-web-player/archive/minimal.zip
	unzip -q minimal.zip
	cd podlove-web-player-minimal; \
	npm install; \
	bower install; \
	gulp
	mv podlove-web-player-minimal/dist/ lib/podlove-web-player
	rm -rf minimal.zip podlove-web-player-minimal/
