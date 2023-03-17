# —— Inspired by ———————————————————————————————————————————————————————————————
# http://fabien.potencier.org/symfony4-best-practices.html
# https://blog.theodo.fr/2018/05/why-you-need-a-makefile-on-your-project/
# https://www.strangebuzz.com/en/snippets/the-perfect-makefile-for-symfony

# https://speakerdeck.com/mykiwi/outils-pour-ameliorer-la-vie-des-developpeurs-symfony?slide=47
# https://github.com/mykiwi/symfony-bootstrapped/blob/master/Makefile

# Makefile autocompletion :
# https://stackoverflow.com/questions/4188324/bash-completion-of-makefile-target
# complete -W "\`grep -oE '^[a-zA-Z0-9_.-]+:([^=]|$)' ?akefile | sed 's/[^a-zA-Z0-9_.-]*$//'\`" make


PHP				= php
PHP_MAX			= php -d memory_limit=1024M
SYMFONY         = $(PHP) bin/console
SYMFONY_BIN     = symfony
COMPOSER        = composer
GIT             = git

# Port pour le serveur symfony local
PORT            = 8014

TARGET_PHP		= 8.2

# Ne sert qu'en local, le fichier '.env.local' n'existe pas sinon
#-include .env.local

## ----------------------------------------------------------
## -- Server ------------------------------------------------
## ----------------------------------------------------------

sfstart: sfstop ## Start local Symfony werserver
	symfony server:start -d --port=$(PORT) --allow-http

sfstop: ## Stop local Symfony werserver
	symfony server:stop

.PHONY: build sfstart sfstop


## ----------------------------------------------------------
## -- Help --------------------------------------------------
## ----------------------------------------------------------

.DEFAULT_GOAL := help
help: ## Affiche la liste des commandes du Makefile
	@echo
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' Makefile \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-15s\033[0m %s\n", $$1, $$2}' \
		| sed -e 's/\[32m##/[33m/'
	@echo

.PHONY: help

