import * as redis from 'redis';
import redisMock from 'redis-mock';
import { promisify } from 'util';
import redisConfig from '../envConfig/redis.js';
import logger from './logger.js';
import appConfig from '../envConfig/app.js';

let client = appConfig.isTest
    ? redisMock.createClient()
    : redis.createClient({
          url: redisConfig.url,
          legacyMode: true,
      });

client.on('error', function (err) {
    logger.error('Error ' + err);
});

const RedisService = {
    getAsync: promisify(client.get).bind(client),
    setAsync: promisify(client.set).bind(client),
};

export const cacheWrapper = (key, getData, ttl = 60) => {
    return async (...args) => {
        let redisKey = key(args);

        const redisResponse = await RedisService.getAsync(redisKey);
        if (redisResponse) {
            logger.debug(`Returning cached response for key ${redisKey}`);
            return JSON.parse(redisResponse);
        }

        const data = await getData(...args);

        await RedisService.setAsync(redisKey, JSON.stringify(data), 'EX', ttl);

        return data;
    };
};
