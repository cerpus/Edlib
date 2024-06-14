# The Edlib 3 Hub

TODO: write the rest of the README

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

## LTI Deep-Linking 1.0 extensions

The following attributes can be used with items of the `LtiLinkItem` type to
communicate extra information that isn't part of the LTI Deep-Linking spec:

* `license`
    * `@id`: `https://spec.edlib.com/lti/vocab#license`
    * `@type`: `http://www.w3.org/2001/XMLSchema#normalizedString`

* `languageIso639_3`
    * `@id`: `https://spec.edlib.com/lti/vocab#languageIso639_3`
    * `@type`: `http://www.w3.org/2001/XMLSchema#normalizedString`

* `published`
    * `@id`: `https://spec.edlib.com/lti/vocab#published`
    * `@type`: `http://www.w3.org/2001/XMLSchema#boolean`

* `shared`
    * `@id`: `https://spec.edlib.com/lti/vocab#shared`
    * `@type`: `http://www.w3.org/2001/XMLSchema#boolean`

* `tag`
    * `@id`: `https://spec.edlib.com/lti/vocab#tag`
    * `@type`: `http://www.w3.org/2001/XMLSchema#normalizedString`
    * Can be either a list of strings, or just a string.

## LTI sample endpoints

Edlib includes some LTI endpoints to make testing and developing easier. These
require an authenticated LTI launch.

* Test Deep Linking with arbitrary contents
  
  <https://hub.edlib.test/lti/samples/deep-link>

* Test basic launches

  <https://hub.edlib.test/lti/samples/presentation>

* Test self-resizing iframes

  <https://hub.edlib.test/lti/samples/resize>

## Resize requests

When iframed, Edlib can emit messages (via [postMessage][1]) requesting the
iframe be resized to fit the content. The format of these messages is:

```json
{
    "action": "resize",
    "scrollHeight": 480
}
```

## Useful resources

* [Laravel 10 documentation](https://laravel.com/docs/10.x)
* [Bootstrap 5.3 documentation](https://getbootstrap.com/docs/5.3/getting-started/introduction/)
* [LTI 1.1 implementation guide](https://www.imsglobal.org/specs/ltiv1p1/implementation-guide)
* [LTI 1.3 specification](http://www.imsglobal.org/spec/lti/v1p3/)
* [LTI 1.3 implementation guide](https://www.imsglobal.org/spec/lti/v1p3/impl/)
* [LTI Content-Item specification](https://www.imsglobal.org/specs/lticiv1p0/specification)
* [LTI Deep Linking specification](http://www.imsglobal.org/spec/lti-dl/v2p0)


[1]: https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage
