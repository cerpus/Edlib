import { db, ApiException } from '@cerpus/edlib-node-utils';
import readiness from '../readiness.js';

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
