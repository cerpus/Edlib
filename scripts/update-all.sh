#!/bin/bash

source $HOME/.nvm/nvm.sh

# cd to this script directory
cd "$(dirname "$0")"

# Go to sourcecode folder
pushd ../sourcecode

# java repos
declare -a javaRepos=(
  "apis/version"
)

for i in "${javaRepos[@]}"
do
  pushd $i
  git pull
  mvn clean package -DskipTests
  popd
done

# Go back to initial folder
popd
