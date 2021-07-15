import { db, ApiException } from '@cerpus/edlib-node-utils';

export default async () => {
    const migrations = await db.migrate.list();

    if (migrations.length !== 2 || migrations[1].length !== 0) {
        throw new ApiException('Migrations are not up to date');
    }

    return true;
};
