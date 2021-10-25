#!/usr/bin/env bash
# This file prepares the repositories.json file that might be used in order to install the module as a symlink during development.
# The repositories.json might be merged with composer.json file using the wikimedia/composer-merge-plugin
set -e

root_dir="$PWD/"

mkdir -p repo-build-dev
echo "{}" > ${root_dir}repo-build-dev/composer.json

module_name="Topsort"
module_dir="$root_dir"

cd ${root_dir}repo-build-dev/
composer config repositories.Extension${module_name} \
    "{\"type\": \"path\",\"url\": \"$module_dir\",\"options\": {\"symlink\": true}}"

mv "${root_dir}repo-build-dev/composer.json" "${root_dir}repo-build-dev/repositories.json"

