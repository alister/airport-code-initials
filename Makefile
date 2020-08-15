NODE_CONTAINER=node
NODE=docker run -it --rm  -v "$(PWD)":/usr/src/app -w /usr/src/app $(NODE_CONTAINER) nodejs
YARN=docker run -it --rm  -v "$(PWD)":/usr/src/app -w /usr/src/app $(NODE_CONTAINER) yarn
NPM=docker run -it --rm  -v "$(PWD)":/usr/src/app -w /usr/src/app $(NODE_CONTAINER) npm

.PHONY: build watch prod

all: clean build

yarn-install:
	$(YARN) install
yarn-outdated:
	$(YARN) outdated
yarn-upgrade:
	$(YARN) upgrade
yarn-audit:
	$(YARN) audit

webserver:
	symfony server:start --allow-http --no-tls --document-root=dist/ --daemon
webserver-stop:
	symfony server:stop
webserver-logs:
	symfony server:log

assets: build
dev: build
build:
	clear
	$(YARN) encore dev
watch:
	clear
	$(YARN) encore dev --watch

# see: https://gist.github.com/ErickPetru/b1b3138ab0fc6c82cd19ea3a1a944ba6
# Copy at https://gist.github.com/alister/8f087283c3b60086589c52155ca8930c
prod:
	clear
	sudo rm -rf dist
	git worktree prune
	git worktree add -f ./dist gh-pages
	$(YARN) encore production
deploy-gh-pages:
	cd dist && git add --all
	# manually commit and push??
	#cd dist && git commit -m "Deploy on gh-pages updated"
	#cd dist && git push origin gh-pages

clean:
	sudo rm -rf dist/*
