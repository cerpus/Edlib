#!/bin/bash
KEY="h5p"
SECRET="secret2"
CA="ca.edlib.test"

if [[ "$*" == *"--fresh"* ]]; then
    (cd sourcecode/hub && php artisan migrate:fresh)
    (cd sourcecode/apis/contentauthor && php artisan migrate:fresh)
fi

cd sourcecode/hub

php artisan edlib:create-admin-user admin@edlib.test

echo -ne "$KEY\n$SECRET\n" | php artisan edlib:add-lti-tool 'Content Author' \
    "https://$CA/lti-content/create" \
    --send-name \
    --send-email \
    --edlib-editable \
    --slug=content-author

php artisan edlib:add-lti-tool-extra content-author 'CA admin' \
    "https://$CA/lti/admin" \
    --slug=ca-admin \
    --admin

cd ../apis/contentauthor

php artisan h5p:library-hub-cache

php artisan h5p:library-install \
    H5P.CoursePresentation
