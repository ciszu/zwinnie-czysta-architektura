#!/bin/bash

BASE_DIR=`pwd`

cd "$BASE_DIR/core/player"
composer install

cd "$BASE_DIR/core/subscription"
composer install

cd "$BASE_DIR/core/web"
composer install