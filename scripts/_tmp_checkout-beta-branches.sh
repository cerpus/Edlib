#!/bin/bash

cd "$(dirname "$0")"

pushd ../sourcecode/not_migrated

pushd h5pviewer
git checkout -b feature/EDL-1033-setup-ca-for-new-resources-api origin/feature/EDL-1033-setup-ca-for-new-resources-api
popd

popd
