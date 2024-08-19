import { db } from '@cerpus/edlib-node-utils';
import request from '../../tests/request.js';

jest.mock('@cerpus/edlib-node-utils', () => ({
    ...jest.requireActual('@cerpus/edlib-node-utils'),
    pubsub: {
        isRunning: () => true,
    },
}));

describe('Test endpoints', () => {
    beforeEach(async () => {
        await db.migrate.latest();
    });
    afterEach(async () => {
        await db.migrate.rollback({}, true);
    });
    describe('Health', () => {
        test('Check if returned http code is 200 when everything is up', async () => {
            const response = await request((c) =>
                c.get(`/_ah/health?probe=readiness`)
            );

            expect(response.statusCode).toBe(200);
        });

        test('Check if returned http code is 503 when db is not migrated', async () => {
            await db.migrate.rollback({}, true);

            const response = await request((c) =>
                c.get(`/_ah/health?probe=readiness`)
            );

            expect(response.statusCode).toBe(503);
        });
    });
    describe('Home', () => {
        test('Home returns http 200', async () => {
            const response = await request((c) => c.get(`/`));

            expect(response.statusCode).toBe(200);
        });
    });
});
