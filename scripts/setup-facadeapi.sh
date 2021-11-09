#!/bin/bash

# cd to this script directory
cd "$(dirname "$0")"

# Go to sourcecode folder
pushd ../sourcecode

mkdir -p not_migrated

pushd not_migrated

git clone ssh://git@app-cerpus-stash.cerpus.net:7999/brain/edlibfacade.git

pushd edlibfacade

mvn clean package -DskipTests
