#!/bin/bash

cd "$(dirname "$0")"

./install-dependencies.sh
./initialize-data-folders.sh
./update-all.sh
./create-aliases.sh

pushd ../localSetup
if [ ! -f .env ];then
  cp .env.example .env
fi
popd

pushd ../sourcecode/apis/contentauthor
ln -s ../vendor/h5p/h5p-core public/h5p-php-library
ln -s ../vendor/h5p/h5p-editor public/h5p-editor-php-library
popd
