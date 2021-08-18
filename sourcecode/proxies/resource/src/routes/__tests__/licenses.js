import request from '../../tests/request.js';

jest.mock('@cerpus/edlib-node-utils', () => {
    const actual = jest.requireActual('@cerpus/edlib-node-utils');
    return {
        ...actual,
        apiClients: {
            ...actual.apiClients,
            license: () => ({
                getAll: async () => [
                    {
                        id: 'PRIVATE',
                        name: 'Private',
                    },
                    {
                        id: 'CC0',
                        name: 'Creative Commons',
                    },
                ],
            }),
        },
    };
});

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
            expect(response.body.length).toBe(2);

            response.body.forEach((license) => {
                expect(license.id).toBeDefined();
                expect(license.name).toBeDefined();
            });
        });
    });
});
