#!/bin/bash

exec java -Xmx${HEAPSPACE_MAX:-512m} -agentlib:jdwp=transport=dt_socket,address=*:5555,suspend=n,server=y -Djava.security.egd=file:/dev/./urandom -jar /app.jar \
    --spring.profiles.active=${PROFILE:-default}