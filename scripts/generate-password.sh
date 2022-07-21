#!/bin/sh

# Generates a password file if one does not exist, and makes it readable to the
# current user only.

set -eu

if [ "$#" -ne 1 ]; then
    echo "Usage:" >&2
    echo "$0 path/to/password/file.txt" >&2
    exit 1
fi

PASSWORD_FILE="$1"

if [ ! -f "$PASSWORD_FILE" ]; then
    if [ -e "$PASSWORD_FILE" ]; then
        echo "Error: $PASSWORD_FILE exists, but is not a file" >&2
        exit 1
    fi

    echo "Password file not found, generating a new one..."
    LC_ALL=C tr -dc A-Za-z0-9 < /dev/urandom | head -c 16 > "$PASSWORD_FILE"
fi

echo "Setting password file permissions..."
chmod 0400 "$PASSWORD_FILE"

exit 0
