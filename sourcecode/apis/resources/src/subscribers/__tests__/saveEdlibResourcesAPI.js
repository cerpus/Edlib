import db from '@cerpus/edlib-node-utils/services/db.js';
import coreInternal from '@cerpus/edlib-node-utils/apiClients/coreInternal/index.js';
import request from '../../tests/request.js';

jest.mock('@cerpus/edlib-node-utils/apiClients/coreInternal/index.js');

describe('Test endpoints', () => {
    beforeEach(async () => {
        await db.migrate.latest();
    });
    afterEach(async () => {
        await db.migrate.rollback({}, true);
    });
    describe('Get mapping from EdLib ID', () => {
        afterEach(() => {
            coreInternal.mockReset();
        });
        test('Check if unknown id throws not found', async () => {
            const structureMock = jest.fn().mockResolvedValue(null);
            coreInternal.mockImplementation(() => ({
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
});
