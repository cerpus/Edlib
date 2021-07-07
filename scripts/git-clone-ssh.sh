#!/bin/bash

cd "$(dirname "$0")"

mkdir -p ../sourcecode/not_migrated
pushd ../sourcecode/not_migrated

TRANSPORT=ssh://git@app-cerpus-stash.cerpus.net:7999/

git clone ${TRANSPORT}edlib/edlib-admin-frontend.git
git clone ${TRANSPORT}edlib/doku-lti-viewer.git
git clone ${TRANSPORT}brain/edlibfacade.git
git clone ${TRANSPORT}edlib/edlibapi-iframe.git
git clone ${TRANSPORT}edlib/edlibapi-auth.git
git clone ${TRANSPORT}edlib/edlibapi-proxy.git
git clone ${TRANSPORT}edlib/edlibapi-recommendations.git
git clone ${TRANSPORT}brain/h5pviewer.git
git clone ${TRANSPORT}brain/licenseapi.git
git clone ${TRANSPORT}brain/metadataservice.git
git clone ${TRANSPORT}rs/re-recommender.git
git clone ${TRANSPORT}rs/re-content-index.git
git clone ${TRANSPORT}brain/versionapi.git
git clone ${TRANSPORT}brain/versionclient.git
git clone ${TRANSPORT}brain/edlibfacade-test.git

popd
