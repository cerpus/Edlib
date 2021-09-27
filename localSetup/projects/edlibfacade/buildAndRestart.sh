#!/bin/bash

# cd to this script directory
cd "$(dirname "$0")"

cd ../../..

pushd sourcecode/not_migrated/edlibfacade
mvn clean package -DskipTests
popd

docker-compose restart edlibfacade