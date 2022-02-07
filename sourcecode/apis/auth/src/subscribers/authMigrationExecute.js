import { buildRawContext } from '../context/index.js';
import { pubsub } from '@cerpus/edlib-node-utils';

export default ({ pubSubConnection }) =>
    async (data) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        const usersWithId = await context.db.user.getByIds(
            data.userIds.map((userId) => userId.to)
        );

        const userIdsToUpdate = data.userIds.filter(
            (userId) => !usersWithId.some((user) => user.id === userId.to)
        );

        let userUpdatedCount = 0;
        if (userIdsToUpdate.length !== 0) {
            userUpdatedCount = await context.db.user.updateUserIds(
                userIdsToUpdate
            );
        }

        await pubsub.publish(
            pubSubConnection,
            'auth_migration_execute_done',
            JSON.stringify({
                id: data.id,
                apiName: 'auth',
                tableName: 'users',
                rowsUpdated: userUpdatedCount,
            })
        );
    };
