#!/bin/sh
set -eu

CA="${CONTENTAUTHOR_HOST}"
KEY="${CONTENTAUTHOR_LTI_KEY}"
SECRET="${CONTENTAUTHOR_LTI_SECRET}"

startup.sh

if [ ! -f "storage/app/first-time-setup-completed" ]; then
  echo -ne "$KEY\n$SECRET\n" | php artisan edlib:add-lti-tool 'Content Author' \
    "https://$CA/lti-content/create" \
    --send-name \
    --send-email \
    --edlib-editable

  echo -ne "$KEY\n$SECRET\n" | php artisan edlib:add-lti-tool 'CA admin (to be moved)' \
    "https://$CA/lti/admin" \
    --send-name \
    --send-email \
    --edlib-editable

  touch storage/app/first-time-setup-completed
fi
