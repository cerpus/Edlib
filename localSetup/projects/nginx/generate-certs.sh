#!/bin/sh
set -e

CA_DIR=/etc/ca
CERTS_DIR=/etc/ssl/private

DOMAINS="\
DNS:edlib.internal.url.local, \
DNS:edlib.internal.auth.local, \
DNS:edlib.internal.resource.local, \
DNS:edlib.internal.lti.local, \
DNS:edlib.internal.doku.local, \
DNS:edlib.internal.version.local, \
DNS:edlibfacade.local, \
DNS:test.edlibfacade.local, \
DNS:contentauthor.local, \
DNS:ca.edlib.local, \
DNS:api.edlib.local, \
DNS:www.edlib.local, \
DNS:npm.components.edlib.local \
"

if [ ! -f "$CA_DIR/ca.key" ]; then
  echo Generating CA key..
  openssl genrsa -out "$CA_DIR/ca.key" 4096

  echo Generating CA cert..
  openssl req \
    -x509 \
    -new \
    -nodes \
    -key "$CA_DIR/ca.key" \
    -sha256 \
    -days 825 \
    -out "$CA_DIR/cacert.pem" \
    -subj '/C=NO/ST=Nordland/L=Alsvaag/O=Cerpus AS/OU=local/CN=cerpus.com/emailAddress=local@cerpus.com'
fi

if \
  [ ! -f "$CERTS_DIR/domains.txt" ] || \
  ! echo "$DOMAINS" | diff -q - "$CERTS_DIR/domains.txt" > /dev/null
then
  echo "$DOMAINS" > "$CERTS_DIR/domains.txt"

  echo "Generating site certificate..."
  rm -f "$CERTS_DIR/cerpus.*"

  OPENSSL_CONFIG=$(mktemp)
  {
    echo '[dn]'
    echo 'CN=localhost'
    echo '[req]'
    echo 'distinguished_name = dn'
    echo '[EXT]'
    echo "subjectAltName = $DOMAINS"
    echo 'keyUsage=digitalSignature'
    echo 'extendedKeyUsage=serverAuth'
  } > "$OPENSSL_CONFIG"

  openssl req \
    -nodes \
    -newkey rsa:2048 \
    -subj '/CN=localhost' \
    -extensions EXT \
    -keyout "$CERTS_DIR/cerpus.key" \
    -out "$CERTS_DIR/cerpus.csr" \
    -config "$OPENSSL_CONFIG"
  rm "$OPENSSL_CONFIG"

  EXTFILE=$(mktemp)
  echo "subjectAltName = $DOMAINS" > "$EXTFILE"
  openssl x509 \
    -req \
    -in "$CERTS_DIR/cerpus.csr" \
    -CA "$CA_DIR/cacert.pem" \
    -CAkey "$CA_DIR/ca.key" \
    -CAcreateserial \
    -out "$CERTS_DIR/cerpus.crt" \
    -days 825 \
    -sha256 \
    -extfile "$EXTFILE"
  rm "$EXTFILE"
fi

exit 0
