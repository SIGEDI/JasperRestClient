# —— Inspired by ———————————————————————————————————————————————————————————————
# https://www.strangebuzz.com/en/snippets/the-perfect-makefile-for-symfony
# Made by Vincent

# Setup ————————————————————————————————————————————————————————————————————————
# Executables
PHP          = php
COMPOSER     = composer
SYMFONY      = $(PHP) bin/console

# Executables: vendors
PHP_CS_FIXER  = $(PHP) ./tools/php-cs-fixer/vendor/bin/php-cs-fixer

# Misc
.DEFAULT_GOAL = help

## —— 🐳& SF Project Makefile ———————————————————————————————————
help: ## Outputs this help screen
	grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-10s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Composer 🧙‍♂️ ————————————————————————————————————————————————————————————
install: ## Install the project (composer, migrations, tooling & build front)
	$(COMPOSER) install
	$(COMPOSER) install --working-dir=tools/php-cs-fixer

## —— Update dependencies (composer + php-cs-fixer) 🧙‍♂ ————————————————————————————————————————————————————————————
update: ## Update vendors according to the composer.json file
	$(COMPOSER) update
	$(COMPOSER) update --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer

lint-php: ## Lint PHP files with php-cs-fixer
	@$(PHP_CS_FIXER) fix --allow-risky=yes --dry-run --config=.php-cs-fixer.dist.php

fix-php: ## Fix PHP files with php-cs-fixer
	$(PHP_CS_FIXER) fix --allow-risky=yes --config=.php-cs-fixer.dist.php
