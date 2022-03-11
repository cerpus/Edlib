import { logger } from '@cerpus/edlib-node-utils';
import {
    ResourceVersionNotCreatedError,
    saveResource,
} from '../services/resource.js';
import { buildRawContext } from '../context/index.js';

export default ({ pubSubConnection }) => async (
    data,
    saveToSearchIndex = true,
    waitForIndex
) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    try {
        await saveResource(context, data, { saveToSearchIndex, waitForIndex });
    } catch (e) {
        if (e instanceof ResourceVersionNotCreatedError) {
            logger.error('An error occurred while saving a resource', e);
            return;
        }

        throw e;
    }
};
