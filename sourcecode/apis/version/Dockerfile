FROM maven:3-jdk-11 AS compile
COPY ./ src/
WORKDIR /src
RUN mvn clean package -DskipTests

## Deploy-container used to run database migrations during deploy

FROM compile AS deploy
WORKDIR /src

CMD mvn liquibase:update

## Test-container used to run test in CI pipeline

FROM compile AS test
VOLUME /result
WORKDIR /src

COPY docker/run-tests.sh /run-tests.sh
RUN chmod +x /run-tests.sh

CMD [ "/run-tests.sh" ]

## App-container used to run application in deployed environments

FROM openjdk:11 as app-prod
EXPOSE 80

COPY --from=0 /src/target/versioning-*.jar app.jar

COPY docker/start-app.sh /start-app.sh
RUN chmod +x /start-app.sh

ENTRYPOINT [ "/start-app.sh" ]

## App-container used to run application with debugging

FROM openjdk:11 as app-dev
EXPOSE 80
EXPOSE 5555

COPY --from=0 /src/target/versioning-*.jar app.jar

COPY docker/start-app-dev.sh /start-app.sh
RUN chmod +x /start-app.sh

ENTRYPOINT [ "/start-app.sh" ]
