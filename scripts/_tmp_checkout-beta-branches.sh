#!/bin/bash

cd "$(dirname "$0")"

pushd ../sourcecode/not_migrated

pushd h5pviewer
git checkout -b feature/EDL-1033-setup-ca-for-new-resources-api origin/feature/EDL-1033-setup-ca-for-new-resources-api
popd

pushd edlibapi-auth
git checkout -b feature/EDL-1036-bruke-nye-strukturen-i-api-et origin/feature/EDL-1036-bruke-nye-strukturen-i-api-et
popd

pushd edlibapi-iframe
git checkout -b feature/EDL-1036-bruke-nye-strukturen-i-api-et origin/feature/EDL-1036-bruke-nye-strukturen-i-api-et
popd

pushd edlibapi-lti
git checkout -b feature/EDL-1036-bruke-nye-strukturen-i-api-et origin/feature/EDL-1036-bruke-nye-strukturen-i-api-et
popd

pushd edlibapi-resources
git checkout -b feature/EDL-1036-bruke-nye-strukturen-i-api-et origin/feature/EDL-1036-bruke-nye-strukturen-i-api-et
popd

popd
