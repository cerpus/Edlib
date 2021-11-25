import * as redis from '../redis.js';

describe('Services', () => {
    describe('redis', () => {
        test('cacheWrapper returns cached data', async () => {
            await redis.cacheWrapper(
                (key) => key,
                async () => 'A'
            )('key');

            const valueFromCache = await redis.cacheWrapper(
                (key) => key,
                async () => 'B'
            )('key');
            expect(valueFromCache).toBe('A');
        });
    });
});
