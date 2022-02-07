import { buildRawContext } from '../context/index.js';
import { pubsub } from '@cerpus/edlib-node-utils';

export default ({ pubSubConnection }) =>
    async (data) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        await pubsub.publish(
            pubSubConnection,
            'auth_migration_info_feedback',
            JSON.stringify({
                id: data.id,
                tables: [
                    {
                        apiName: 'resource',
                        tableName: 'resourceVersions',
                        rowCount:
                            await context.db.resourceVersion.getCountForOwners(
                                data.userIds
                            ),
                    },
                    {
                        apiName: 'resource',
                        tableName: 'resourceVersionCollaborators',
                        rowCount:
                            await context.db.resourceVersionCollaborator.getCountForTenants(
                                data.userIds
                            ),
                    },
                    {
                        apiName: 'resource',
                        tableName: 'resourceCollaborators',
                        rowCount:
                            await context.db.resourceCollaborator.getCountForTenants(
                                data.userIds
                            ),
                    },
                ],
            })
        );
    };
