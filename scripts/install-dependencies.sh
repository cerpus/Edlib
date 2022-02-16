#!/bin/bash

cd "$(dirname "$0")"

# Fix permissions to docker deamon
if ! command -v yarn &> /dev/null
then
  sudo snap install docker
  sudo groupadd docker
  sudo usermod -aG docker $USER
fi

if ! command -v python3 &> /dev/null
then
  sudo snap install python38
fi

# Install packages
sudo apt update
sudo apt install -y maven git wget curl php-memcached php-amqplib php-intl php-oauth php-bcmath php-gd php-xdebug docker-compose

# Install NVM
if [ -z ${NVM_DIR+x} ]; then
  echo "Can't find NVM. Automatically installing"
  curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | bash
  export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
fi
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" # This loads nvm

# Install required node versions with nvm
nvm install 10
nvm install 11
nvm install 12
nvm install 14
nvm install 16

# Install yarn if needed
if ! command -v yarn &> /dev/null
then
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
    sudo apt update && sudo apt install -y yarn
fi

# Install yarn if needed
if ! command -v composer &> /dev/null
then
  curl -sS https://getcomposer.org/download/1.10.22/composer.phar -o composer
  chmod +x composer
  sudo mv composer /usr/local/bin/composer
fi
