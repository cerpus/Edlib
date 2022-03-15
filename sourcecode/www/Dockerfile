FROM node:16.13 as base
WORKDIR /app
COPY . .
RUN yarn

FROM base as build
WORKDIR /app
RUN yarn build --noninteractive

FROM build as prod
EXPOSE 80
CMD yarn start:prod

FROM node:16.13 as dev
WORKDIR /app
EXPOSE 3000
CMD yarn; yarn start
