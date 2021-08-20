#!/bin/bash
set -e

cd "$(dirname "$0")"
cd ..

CA_DIR=data/nginx/ca
CERTS_DIR=data/nginx/certs

if [ ! -f "$CA_DIR/cacert.pem" ]; then
  echo "$CA_DIR/cacert.pem does not exist. Did you start the nginx container?"
  exit 1
fi

echo "Copying $CA_DIR/cacert.pem to /usr/local/share/ca-certificates/cerpus-dev"
sudo cp $CA_DIR/cacert.pem /usr/local/share/ca-certificates/cerpus.crt
sudo update-ca-certificates --fresh

