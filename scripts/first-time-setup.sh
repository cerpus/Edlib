#!/bin/bash

cd "$(dirname "$0")"

./initialize-data-folders.sh
./git-clone-ssh.sh
./update-all.sh
./update-certs.sh

pushd ../localSetup
if [ ! -f .env ];then
  cp .env.example .env
fi
popd

pushd ../sourcecode/not_migrated/h5pviewer
ln -s ../vendor/h5p/h5p-core public/h5p-php-library
ln -s ../vendor/h5p/h5p-editor public/h5p-editor-php-library
popd
