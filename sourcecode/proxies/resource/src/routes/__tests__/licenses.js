import request from '../../tests/request.js';

describe('Test endpoints', () => {
    describe('Licenses', () => {
        test('Get all unauthorized if no token', async () => {
            const response = await request((c) =>
                c.get('/resources/v1/filters/licenses')
            );

            expect(response.statusCode).toBe(401);
        });

        test('Get all returns expected response', async () => {
            const response = await request((c) =>
                c
                    .get('/resources/v1/filters/licenses')
                    .set({ Authorization: 'Bearer test' })
            );

            expect(response.statusCode).toBe(200);

            response.body.forEach((license) => {
                expect(license.id).toBeDefined();
                expect(license.name).toBeDefined();
            });
        });
    });
});
