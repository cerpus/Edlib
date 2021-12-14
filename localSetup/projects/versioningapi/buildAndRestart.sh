#!/bin/bash

# cd to this script directory
cd "$(dirname "$0")"

cd ../..

pushd sourcecode/apis/version
mvn clean package -DskipTests
popd

docker-compose restart versionapi
