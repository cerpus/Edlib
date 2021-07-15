import request from '../../tests/request.js';

describe('Test endpoints', () => {
    describe('Recommendations 1', () => {
        test('Get unauthorized if no token', async () => {
            const response = await request((c) =>
                c.post('/recommendations/v1/recommendations')
            );

            expect(response.statusCode).toBe(401);
        });
    });
});
