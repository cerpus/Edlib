#!/bin/bash

cd "$(dirname "$0")"

pushd ..

mkdir -p data

pushd data

mkdir -p elasticsearch

mkdir -p contentauthor
mkdir -p contentauthor/h5pstorage
cp -R ../sourcecode/not_migrated/h5pviewer/storage contentauthor

mkdir -p licenseapi
cp -R ../sourcecode/not_migrated/licenseapi/storage licenseapi


popd
