# The Edlib 3 Hub

TODO: write the rest of the README

## Tag migration

Edlibs from before September 2025 should have `php artisan edlib:migrate-tags`
run on them to ensure content types on old contents are correctly displayed and
filterable.

## Using ngrok for development

Social login requires the dev environment to be available over the web for some
providers to allow OAuth redirects. ngrok can be used for this purpose:

```shell
docker run --rm -e NGROK_AUTHTOKEN=your-token-here --network=edlib_default \
    ngrok/ngrok:latest http hub.edlib.test:443 \
    --hostname=edlib-hub-your-domain-here.ngrok.dev
```

## LTI param sources

* Proper LTI requests: `$request->attributes->get('lti')`
* LTI sessions after initial launch: `$request->session()->get('lti')`

## LTI sample endpoints

Edlib includes some LTI endpoints to make testing and developing easier. These
require an authenticated LTI launch.

* Test Deep Linking with arbitrary contents
  
  <https://hub.edlib.test/lti/samples/deep-link>

* Test basic launches

  <https://hub.edlib.test/lti/samples/presentation>

* Test self-resizing iframes

  <https://hub.edlib.test/lti/samples/resize>

## Useful resources

* [Laravel 10 documentation](https://laravel.com/docs/10.x)
* [Bootstrap 5.3 documentation](https://getbootstrap.com/docs/5.3/getting-started/introduction/)
* [LTI 1.1 implementation guide](https://www.imsglobal.org/specs/ltiv1p1/implementation-guide)
* [LTI 1.3 specification](http://www.imsglobal.org/spec/lti/v1p3/)
* [LTI 1.3 implementation guide](https://www.imsglobal.org/spec/lti/v1p3/impl/)
* [LTI Content-Item specification](https://www.imsglobal.org/specs/lticiv1p0/specification)
* [LTI Deep Linking specification](http://www.imsglobal.org/spec/lti-dl/v2p0)


[1]: https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage
