import redis from 'redis';
import redisMock from 'redis-mock';
import { promisify } from 'util';
import chalk from 'chalk';
import redisConfig from '../envConfig/redis.js';
import logger from './logger.js';
import appConfig from '../envConfig/app.js';

let client = appConfig.isTest
    ? redisMock.createClient()
    : redis.createClient({
          url: redisConfig.url,
          retry_strategy: function (options) {
              logger.debug('Redis error', options);
              if (options.error && options.error.code === 'ECONNREFUSED') {
                  // End reconnecting on a specific error and flush all commands with
                  // a individual error
                  return new Error('The server refused the connection');
              }
              if (options.total_retry_time > 1000 * 60 * 60) {
                  // End reconnecting after a specific timeout and flush all commands
                  // with a individual error
                  return new Error('Retry time exhausted');
              }
              if (options.attempt > 10) {
                  // End reconnecting with built in error
                  return undefined;
              }
              // reconnect after
              return Math.min(options.attempt * 100, 3000);
          },
      });

client.on('error', function (err) {
    logger.error('Error ' + err);
});

const RedisService = {
    ...client,
    getAsync: promisify(client.get).bind(client),
    setAsync: promisify(client.set).bind(client),
    keysAsync: promisify(client.keys).bind(client),
    ttlAsync: promisify(client.ttl).bind(client),
};

export default RedisService;

export const cacheWrapper = (key, getData, ttl = 60) => {
    return async (...args) => {
        let redisKey = key(args);

        const redisResponse = await RedisService.getAsync(redisKey);
        if (redisResponse) {
            logger.debug(
                chalk.cyan(`Returning cached response for key ${redisKey}`)
            );
            return JSON.parse(redisResponse);
        }

        const data = await getData(...args);

        await RedisService.setAsync(redisKey, JSON.stringify(data), 'EX', ttl);

        return data;
    };
};
