import { db, pubsub, ApiException } from './node-utils/index.js';

export default async () => {
    const migrations = await db.migrate.list();

    if (migrations.length !== 2 || migrations[1].length !== 0) {
        throw new ApiException('Migrations are not up to date');
    }

    if (!pubsub.isRunning()) {
        throw new ApiException('Rabbitmq connection is down');
    }

    return true;
};
