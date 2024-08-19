import { db, ApiException } from '@cerpus/edlib-node-utils';
import readiness from '../readiness.js';

jest.mock('@cerpus/edlib-node-utils', () => ({
    ...jest.requireActual('@cerpus/edlib-node-utils'),
    pubsub: {
        isRunning: () => true,
    },
}));

describe('Readiness', () => {
    test('Throws when database isnt migrated', async () => {
        await expect(readiness()).rejects.toBeInstanceOf(ApiException);
    });
    test("Doesn't throw when initialized", async () => {
        await db.migrate.latest();
        await expect(readiness()).resolves.toBe(true);
        await db.migrate.rollback({}, true);
    });
});
