import { buildRawContext } from '../context/index.js';
import * as elasticSearchService from '../services/elasticSearch.js';

/**
 * Subscriber to register tracking on resources.
 *
 * @param pubSubConnection
 * @returns {function(*): Promise<void>}
 */
const updateElasticsearchForResource =
    ({ pubSubConnection }) =>
    async ({ resourceId }) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        if (!resourceId) {
            return;
        }

        const resource = await context.db.resource.getById(resourceId);

        if (!resource) {
            return;
        }

        await elasticSearchService.syncResource(context, resource);
    };

export default updateElasticsearchForResource;
