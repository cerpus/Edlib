#!/bin/bash

cd "$(dirname "$0")"

# Install NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | bash
. $NVM_DIR/nvm.sh

# Install required node versions with nvm
nvm install 10
nvm install 12
nvm install 14
nvm install 16

# Install yarn if needed
if ! command -v yarn &> /dev/null
then
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
    sudo apt update && sudo apt install -y yarn
    exit
fi
