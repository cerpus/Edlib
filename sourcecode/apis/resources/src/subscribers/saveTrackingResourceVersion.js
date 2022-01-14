import { buildRawContext } from '../context/index.js';
import { errors } from '@elastic/elasticsearch';
import { logger } from '@cerpus/edlib-node-utils';

/**
 * Subscriber to register tracking on resources.
 *
 * @param pubSubConnection
 * @returns {function(*): Promise<void>}
 */
export default ({ pubSubConnection }) =>
    async ({ resourceVersionId, externalReference }) => {
        const context = buildRawContext({}, {}, { pubSubConnection });

        const resourceVersion = await context.db.resourceVersion.getById(
            resourceVersionId
        );

        if (!resourceVersion) {
            logger.error(
                'resourceVersionId does not refer to an actual resource version',
                { resourceVersionId }
            );
            return;
        }

        const existingView =
            externalReference &&
            (await context.db.trackingResourceVersion.getByExternalReference(
                externalReference
            ));

        if (existingView) {
            logger.error('this view has already been tracked', {
                externalReference,
            });
            return;
        }

        await context.db.trackingResourceVersion.create({
            externalReference,
            resourceVersionId,
        });

        try {
            await context.services.elasticsearch.incrementView(
                resourceVersion.resourceId
            );
        } catch (e) {
            if (e instanceof errors.ResponseError && e.statusCode === 404) {
                logger.error('Resource was not found in elasticsearch', {
                    resourceId: resourceVersion.resourceId,
                });
                return;
            }

            throw e;
        }
    };
