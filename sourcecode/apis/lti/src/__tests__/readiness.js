import db from '@cerpus/edlib-node-utils/services/db.js';
import readiness from '../readiness.js';
import { ApiException } from '@cerpus/edlib-node-utils/exceptions/index.js';

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
