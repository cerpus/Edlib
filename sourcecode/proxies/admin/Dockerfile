FROM node:16-alpine as base
WORKDIR /app
COPY . .
RUN yarn

FROM base as build
WORKDIR /app
RUN yarn build
RUN rm /app/build/static/**/*.map

FROM nginx:alpine as prod
RUN apk update; apk add bash curl
COPY --from=build /app/build /usr/share/nginx/html
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./createEnvFile.sh /createEnvFile.sh
EXPOSE 80
CMD bash /createEnvFile.sh /usr/share/nginx/html; nginx -g "daemon off;"

FROM node:16-alpine as dev
ENV NODE_EXTRA_CA_CERTS="/usr/local/share/ca-certificates/cacert.pem"
WORKDIR /app
RUN apk --no-cache add curl
EXPOSE 3000
EXPOSE 3001
CMD yarn; yarn start
