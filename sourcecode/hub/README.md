# The Edlib 3 Hub

TODO: write the rest of the README

## Browser testing via Docker

1. Ensure you have Google Chrome/Chromium installed.

2. Download [ChromeDriver](https://chromedriver.chromium.org/home) and unpack 
   it.

3. Start ChromeDriver.

   ```bash
   ./chromedriver --allowed-ips= --allowed-origins='*'
   ```

4. Make sure APP_URL is set to the correct location. It should be the URL you
   open in your browser for development.

   ```dotenv
   APP_URL=https://hub.edlib.local
   ```
5. Run the browser tests.

## Using ngrok for development

Social login requires the dev environment to be available over the web for some
providers to allow OAuth redirects. ngrok can be used for this purpose:

```shell
docker run --rm -e NGROK_AUTHTOKEN=your-token-here --network=edlib_default \
    ngrok/ngrok:latest http hub.edlib.local:443 \
    --hostname=edlib-hub-your-domain-here.ngrok.dev
```

## Useful resources

* [Laravel 10 documentation](https://laravel.com/docs/10.x)
* [Bootstrap 5.3 documentation](https://getbootstrap.com/docs/5.3/getting-started/introduction/)
* [LTI 1.1 implementation guide](https://www.imsglobal.org/specs/ltiv1p1/implementation-guide)
* [LTI 1.3 specification](http://www.imsglobal.org/spec/lti/v1p3/)
* [LTI 1.3 implementation guide](https://www.imsglobal.org/spec/lti/v1p3/impl/)
* [LTI Content-Item specification](https://www.imsglobal.org/specs/lticiv1p0/specification)
* [LTI Deep Linking specification](http://www.imsglobal.org/spec/lti-dl/v2p0)
