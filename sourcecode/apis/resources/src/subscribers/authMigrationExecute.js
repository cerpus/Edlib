import { buildRawContext } from '../context/index.js';
import { pubsub } from '@cerpus/edlib-node-utils';

export default ({ pubSubConnection }) =>
    async (data) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        await pubsub.publish(
            pubSubConnection,
            'auth_migration_execute_done',
            JSON.stringify({
                id: data.id,
                apiName: 'resource',
                tableName: 'resourceVersions',
                rowsUpdated: await context.db.resourceVersion.updateOwnerIds(
                    data.userIds
                ),
            })
        );
        await pubsub.publish(
            pubSubConnection,
            'auth_migration_execute_done',
            JSON.stringify({
                id: data.id,
                apiName: 'resource',
                tableName: 'resourceVersionCollaborators',
                rowsUpdated:
                    await context.db.resourceVersionCollaborator.updateTenantIds(
                        data.userIds
                    ),
            })
        );
        await pubsub.publish(
            pubSubConnection,
            'auth_migration_execute_done',
            JSON.stringify({
                id: data.id,
                apiName: 'resource',
                tableName: 'resourceCollaborators',
                rowsUpdated:
                    await context.db.resourceCollaborator.updateTenantIds(
                        data.userIds
                    ),
            })
        );
    };
