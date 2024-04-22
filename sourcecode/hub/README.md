# The Edlib 3 Hub

TODO: write the rest of the README

## Browser testing via Docker

1. Ensure you have Google Chrome/Chromium installed.

2. Install ChromeDriver. Make sure this matches the version of Google Chrome/Chromium installed.
   1. Either use Laravel Dusk to install. See
      [Laravel Dusk documenteation](https://laravel.com/docs/11.x/dusk#managing-chromedriver-installations) for more options.
      ```bash
      docker compose exec hub php artisan dusk:chrome-driver
      ```
   2. or install manually by downloading [ChromeDriver](https://googlechromelabs.github.io/chrome-for-testing/) and unpacking it.

3. Start ChromeDriver. If installed with Laravel Dusk it's located in the `vendor/laravel/dusk/bin/` folder.
   When running use parameters `--allowed-ips= --allowed-origins='*'`

4. Make sure <https://hub-test.edlib.test> loads in your browser. It should look
   the same as the regular hub, but should not share data with it.

5. Run the browser tests
    ```bash
    docker compose exec -e APP_ENV=testing hub php artisan dusk
   ```


### Headless browser testing

1. Create a `docker-compose.override.yml` in Edlib root (where the `docker-compose.yml` file is) with the following content
    ```yaml
    services:
      hub:
        extra_hosts:
          - "host.docker.internal:host-gateway"
    ```

2. In the environment file `.env.testing` add or uncomment `DUSK_HEADLESS_DISABLED=false`

3. Follow the steps for [Browser testing via Docker](#browser-testing-via-docker)


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

* `tag`
    * `@id`: `https://spec.edlib.com/lti/vocab#languageIso639_3`
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

## Useful resources

* [Laravel 10 documentation](https://laravel.com/docs/10.x)
* [Bootstrap 5.3 documentation](https://getbootstrap.com/docs/5.3/getting-started/introduction/)
* [LTI 1.1 implementation guide](https://www.imsglobal.org/specs/ltiv1p1/implementation-guide)
* [LTI 1.3 specification](http://www.imsglobal.org/spec/lti/v1p3/)
* [LTI 1.3 implementation guide](https://www.imsglobal.org/spec/lti/v1p3/impl/)
* [LTI Content-Item specification](https://www.imsglobal.org/specs/lticiv1p0/specification)
* [LTI Deep Linking specification](http://www.imsglobal.org/spec/lti-dl/v2p0)
