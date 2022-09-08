FROM node:16-alpine as build
WORKDIR /app
COPY package.json yarn.lock ./
RUN yarn install
COPY . .
RUN yarn build

FROM nginx:alpine as prod
RUN apk update; apk add bash
COPY --from=build /app/build /usr/share/nginx/html
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD nginx -g "daemon off;"

FROM node:16-alpine as dev
WORKDIR /app
EXPOSE 3000
CMD yarn; yarn start
