FROM debian:12

RUN set -eux; \
    export DEBIAN_FRONTEND=noninteractive; \
    apt-get update; \
    apt-get -y install chromium-driver libnss3-tools; \
    rm -rf /var/lib/apt/lists/*;

RUN useradd -ms /bin/bash chromeuser

COPY docker-entrypoint.sh /usr/local/bin/

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["chromedriver", "--allowed-ips=", "--allowed-origins=*", "--log-level=WARNING", "--port=9515"]
