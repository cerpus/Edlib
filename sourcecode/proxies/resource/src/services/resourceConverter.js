import Sentry from '@sentry/node';
import resourceTypes, { h5pTypes } from '../constants/resourceTypes.js';
import resourceCapabilities from '../constants/resourceCapabilities.js';
import { NotFoundException } from '@cerpus/edlib-node-utils';

export const getCapabilities = ({ resourceType, contentAuthorType }) => {
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

export const fromCore = async (context, resources) => {
    return await Promise.all(
        resources
            .map((r) => ({
                ...r,
                edlibId: r.uuid,
                uuid: undefined,
            }))
            .map(async (resource) => {
                let extraInfo = {
                    license: null,
                };

                if (Array.isArray(resource.licenses)) {
                    extraInfo.license = resource.licenses[0] || null;
                } else if (!extraInfo.license) {
                    try {
                        extraInfo.license = await context.services.license.get(
                            resource.edlibId
                        );
                    } catch (e) {
                        if (!(e instanceof NotFoundException)) {
                            console.error(e);
                            Sentry.captureException(e);
                        }
                    }
                }

                try {
                    const resourceInfo = await context.services.coreInternal.resource.info(
                        resource.edlibId
                    );

                    extraInfo = {
                        ...extraInfo,
                        ...resourceInfo,
                    };
                } catch (e) {
                    if (!(e instanceof NotFoundException)) {
                        console.error(
                            `Get Resource info for id ${resource.edlibId} failed`
                        );
                        console.error(e);
                        Sentry.captureException(e);
                    }
                }

                delete resource.licenses;
                delete resource.className;
                return {
                    ...resource,
                    resourceCapabilities: getCapabilities(resource),
                    ...extraInfo,
                };
            })
    );
};
