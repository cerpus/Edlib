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
