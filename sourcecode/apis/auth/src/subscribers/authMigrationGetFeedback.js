import { buildRawContext } from '../context/index.js';
import { pubsub } from '@cerpus/edlib-node-utils';

export default ({ pubSubConnection }) =>
    async (data) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        const users = await context.db.user.getByIds(data.userIds);

        await pubsub.publish(
            pubSubConnection,
            'auth_migration_info_feedback',
            JSON.stringify({
                id: data.id,
                tables: [
                    {
                        apiName: 'auth',
                        tableName: 'users',
                        rowCount: users.length,
                    },
                ],
            })
        );
    };
