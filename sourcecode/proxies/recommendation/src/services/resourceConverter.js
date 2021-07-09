import resourceTypes, { h5pTypes } from '../constants/resourceTypes.js';
import resourceCapabilities from '../constants/resourceCapabilities.js';
import { logger } from '@cerpus/edlib-node-utils';
import moment from 'moment';

const getCapabilities = ({ resourceType, contentAuthorType }) => {
    let capabilities = [];

    if (resourceType !== resourceTypes.URL) {
        capabilities.push(resourceCapabilities.VERSION);
        capabilities.push(resourceCapabilities.EDIT);
    }

    if (
        resourceType !== resourceTypes.H5P ||
        contentAuthorType !== h5pTypes.questionset
    ) {
        capabilities.push(resourceCapabilities.VIEW);
    }

    return capabilities;
};

export const fromRecommender = async (context, resources) => {
    return (
        await Promise.all(
            resources.map(async (resource) => {
                let coreResource;
                try {
                    coreResource = await context.services.coreInternal.resource.fromExternalIdInfo(
                        resource.type,
                        resource.id
                    );
                } catch (e) {
                    logger.error(e);
                    return null;
                }

                return {
                    edlibId: coreResource.uuid,
                    name: resource.title,
                    type: resource.type,
                    externalId: resource.id,
                    resourceCapabilities: getCapabilities(coreResource),
                    title: undefined,
                    tags: undefined,
                    updatedAt: moment
                        .unix(resource.last_updated_at)
                        .toISOString(),
                    createdAt: coreResource.created,
                    license: resource.license,
                };
            })
        )
    ).filter(Boolean);
};
