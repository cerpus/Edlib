import { db, apiClients } from '@cerpus/edlib-node-utils';
import request from '../../tests/request.js';

jest.mock('@cerpus/edlib-node-utils', () => ({
    ...jest.requireActual('@cerpus/edlib-node-utils'),
    apiClients: {
        coreInternal: jest.fn(),
        version: jest.fn(),
        edlibAuth: jest.fn(),
    },
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
    describe('Get mapping from EdLib ID', () => {
        afterEach(() => {
            apiClients.coreInternal.mockReset();
        });
        test('Check if unknown id throws not found', async () => {
            const structureMock = jest.fn().mockResolvedValue(null);
            apiClients.coreInternal.mockImplementation(() => ({
                resource: {
                    structure: structureMock,
                },
            }));

            await request((c) =>
                c.get(`/v1/resources/28c048d9-e58c-412d-8b90-4f9c0349460f`)
            );

            const response = await request((c) =>
                c.get(`/v1/resources/28c048d9-e58c-412d-8b90-4f9c0349460f`)
            );

            expect(response.statusCode).toBe(404);
        });
    });
    describe('Get mapping from external Id', () => {
        afterEach(() => {
            apiClients.coreInternal.mockReset();
        });
        test('Check if unknown id throws not found', async () => {
            const fromExternalIdInfoMock = jest.fn().mockResolvedValue(null);

            apiClients.coreInternal.mockImplementation(() => ({
                resource: {
                    fromExternalIdInfo: fromExternalIdInfoMock,
                },
            }));

            const response = await request((c) =>
                c.get(`/v1/resources-from-external/contentAuthor/219`)
            );

            expect(response.statusCode).toBe(404);
        });
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
