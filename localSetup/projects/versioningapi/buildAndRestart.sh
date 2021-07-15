#!/bin/bash

# cd to this script directory
cd "$(dirname "$0")"

cd ../..

pushd sourcecode/versionapi
mvn clean package -DskipTests
popd

docker-compose restart versioningapi
