#!/bin/bash

BASE_DIR=`pwd`

cd "$BASE_DIR/core/player"
composer install

cd "$BASE_DIR/core/subscription"
rm composer.lock
composer install

cd "$BASE_DIR/core/marketplace"
rm composer.lock
rm -rf vendor/mozartify
composer install

cd "$BASE_DIR/web"
rm composer.lock
rm -rf vendor/mozartify
composer install