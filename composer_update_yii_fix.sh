#!/bin/bash
composer global require "fxp/composer-asset-plugin:*"
composer update
composer global remove "fxp/composer-asset-plugin"
rm -rf repo/bower*
cp -R vendor/bower* repo/
