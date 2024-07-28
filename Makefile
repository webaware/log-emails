PKG_NAME			:= log-emails
PKG_VERSION			:= $(shell sed -rn 's/^Version: (.*)/\1/p' $(PKG_NAME).php)

ZIP					:= .dist/$(PKG_NAME)-$(PKG_VERSION).zip
FIND_PHP			:= find . -path ./vendor -prune -o -path ./node_modules -prune -o -path './.*' -o -name '*.php'
SRC_PHP				:= $(shell $(FIND_PHP) -print)

.PHONY: all lint lint-php lint-css zip wpsvn css

all:
	@echo please see Makefile for available builds / commands

clean:
	rm -f static/css/* static/js/* languages/$(PKG_NAME).pot .make-flag*

# release product

zip: $(ZIP)

$(ZIP): $(SRC_PHP) static/css/* *.md *.txt
	rm -rf .dist
	mkdir .dist
	git archive HEAD --prefix=$(PKG_NAME)/ --format=zip -9 -o $(ZIP)

# WordPress plugin directory

wpsvn: lint
	svn up .wordpress.org
	rm -rf .wordpress.org/trunk
	mkdir .wordpress.org/trunk
	git archive HEAD --format=tar | tar x --directory=.wordpress.org/trunk

# build CSS targets

CSS_SRC_DIR		:= source/scss
CSS_TGT_DIR		:= static/css
CSS_SRCS		:= $(shell find source/scss -maxdepth 1 -name '[a-z]*.scss' -print)
CSS_DEPS		:= $(shell find source/scss -maxdepth 1 -name '*.scss' -print)
CSS_TGTS		:= $(CSS_SRCS:$(CSS_SRC_DIR)/%.scss=$(CSS_TGT_DIR)/%.css)
CSS_LINT		:= npx stylelint --config .stylelintrc.yml "$(CSS_SRC_DIR)/**/*.scss"

css: .make-flag-css

.make-flag-css: $(CSS_DEPS)
	$(CSS_LINT)
	sass $(foreach source,$(CSS_SRCS),$(source):$(source:$(CSS_SRC_DIR)/%.scss=$(CSS_TGT_DIR)/%.css)) --style=expanded --no-charset
	npx postcss $(CSS_TGTS) --use autoprefixer --replace --map
	cd $(CSS_TGT_DIR); npx cleancss -O2 --format beautify --source-map --batch --batch-suffix '' $(notdir $(CSS_TGTS))
	cd $(CSS_TGT_DIR); npx cleancss --batch --batch-suffix '.min' $(notdir $(CSS_TGTS))
	@touch .make-flag-css

# code linters

lint: lint-php lint-css

lint-php:
	@echo PHP lint...
	@$(FIND_PHP) -exec php7.4 -l '{}' \; >/dev/null
	@$(FIND_PHP) -exec php8.3 -l '{}' \; >/dev/null
	@vendor/bin/phpcs -ps
	@vendor/bin/phpcs -ps --standard=phpcs-5.2.xml

lint-css:
	@echo CSS lint...
	@$(CSS_LINT)

