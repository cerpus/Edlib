import knex from '@cerpus-private/edlib-node-utils/services/db.js';
import { ApiException } from '@cerpus-private/edlib-node-utils/exceptions/index.js';

export default async () => {
    const migrations = await knex.migrate.list();

    if (migrations.length !== 2 || migrations[1].length !== 0) {
        throw new ApiException('Migrations are not up to date');
    }

    return true;
};
