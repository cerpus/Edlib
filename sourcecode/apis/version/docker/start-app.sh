#!/bin/bash

exec java -Xmx${HEAPSPACE_MAX:-512m} -Djava.security.egd=file:/dev/./urandom -jar /app.jar \
    --spring.profiles.active=${PROFILE:-default} \
    --spring.liquibase.enabled=false