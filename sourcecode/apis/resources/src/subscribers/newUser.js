import { buildRawContext } from '../context/index.js';
import * as elasticSearchService from '../services/elasticSearch.js';

/**
 * Subscriber to update data when a new user is added in the user database.
 *
 * @param pubSubConnection
 * @returns {function(*): Promise<void>}
 */
export default ({ pubSubConnection }) => async (data) => {
    if (
        !data.user ||
        !data.user.email ||
        !data.user.id ||
        data.user.email.length === 0
    ) {
        return;
    }
    const context = buildRawContext({}, {}, { pubSubConnection });

    const dbCollaboratorsWithMissingId = await context.db.resourceVersionCollaborator.getForEmailWithMissingTenantWithResourceId(
        data.user.email
    );

    console.log(dbCollaboratorsWithMissingId);
    await Promise.all(
        dbCollaboratorsWithMissingId.map(
            async (dbCollaboratorWithMissingId) => {
                await context.db.resourceVersionCollaborator.update(
                    dbCollaboratorWithMissingId.id,
                    {
                        tenantId: data.user.id,
                    }
                );

                return await elasticSearchService.syncResource(
                    context,
                    await context.db.resource.getById(
                        dbCollaboratorWithMissingId.resourceId
                    ),
                    false
                );
            }
        )
    );
};
