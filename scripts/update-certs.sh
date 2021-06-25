#!/bin/bash

cd "$(dirname "$0")"
cd ..

CA_DIR=data/nginx/ca
CERTS_DIR=data/nginx/certs

mkdir -p $CA_DIR
mkdir -p $CERTS_DIR

ls -l $CA_DIR

if [ ! -f $CA_DIR/ca.key ];then
  echo Generating CA key..
  openssl genrsa -out $CA_DIR/ca.key 4096
  echo Generating CA cert..
  openssl req -x509 -new -nodes -key $CA_DIR/ca.key -sha256 -days 3650 -out $CA_DIR/cacert.pem \
    -subj "/C=NO/ST=Nordland/L=Alsvaag/O=Cerpus AS/OU=local/CN=cerpus.com/emailAddress=local@cerpus.com"
  echo CA is complete.
else
  echo "Certificate issuer already exists"
fi

DOMAINS=(
  "DNS:edlib.internal.url.local"
  "DNS:edlib.internal.auth.local"
  "DNS:edlib.internal.resource.local"
  "DNS:edlib.internal.lti.local"
  "DNS:edlib.internal.doku.local"
  "DNS:edlibfacade.local"
  "DNS:test.edlibfacade.local"
  "DNS:localhost"
  "DNS:contentauthor.local"
  "DNS:api.edlib.local"
)
DNS=$(IFS=, ; echo "${DOMAINS[*]}")

OPENSSL_CONFIG=$(mktemp)
    {
        echo '[dn]'
        echo 'CN=localhost'
        echo '[req]'
        echo 'distinguished_name = dn'
        echo '[EXT]'
	      echo "subjectAltName = $DNS"
        echo 'keyUsage=digitalSignature'
        echo 'extendedKeyUsage=serverAuth'
    } > "$OPENSSL_CONFIG"
    openssl req \
        -nodes \
        -newkey rsa:2048 \
        -subj '/CN=localhost' \
        -extensions EXT \
        -keyout $CERTS_DIR/cerpus.key \
        -out $CERTS_DIR/cerpus.csr \
        -config "$OPENSSL_CONFIG"

    EXTFILE=$(mktemp)
    echo "subjectAltName = $DNS" > "$EXTFILE"

    openssl x509 -req -in $CERTS_DIR/cerpus.csr -CA $CA_DIR/cacert.pem -CAkey $CA_DIR/ca.key -CAcreateserial \
-out $CERTS_DIR/cerpus.crt -days 3649 -sha256 -extfile "$EXTFILE"
    rm "$OPENSSL_CONFIG" "$EXTFILE"

echo "Copying $CA_DIR/cacert.pem to /usr/local/share/ca-certificates/cerpus-dev"
sudo cp $CA_DIR/cacert.pem /usr/local/share/ca-certificates/cerpus.crt
sudo update-ca-certificates --fresh

