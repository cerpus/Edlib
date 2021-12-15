import { buildRawContext } from '../context/index.js';
import { errors } from '@elastic/elasticsearch';

/**
 * Subscriber to register tracking on resources.
 *
 * @param pubSubConnection
 * @returns {function(*): Promise<void>}
 */
export default ({ pubSubConnection }) => async ({
    resourceVersionId,
    externalReference,
}) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    const resourceVersion = await context.db.resourceVersion.getById(
        resourceVersionId
    );

    if (!resourceVersion) {
        return;
    }

    const existingView =
        externalReference &&
        (await context.db.trackingResourceVersion.getByExternalReference(
            externalReference
        ));

    if (existingView) {
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
            return;
        }

        throw e;
    }
};
