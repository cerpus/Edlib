#!/bin/bash

cd "$(dirname "$0")"

mkdir -p ../sourcecode/not_migrated
pushd ../sourcecode/not_migrated

TRANSPORT=ssh://git@app-cerpus-stash.cerpus.net:7999/

git clone ${TRANSPORT}brain/edlibfacade.git
git clone ${TRANSPORT}brain/licenseapi.git

popd
