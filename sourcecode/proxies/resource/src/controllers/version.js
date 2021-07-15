import { NotFoundException } from '@cerpus/edlib-node-utils';

const decorateResource = async (context, resource) => {
    const idMapping = await context.services.id.getForExternal(
        resource.externalSystem,
        resource.externalReference
    );

    if (!idMapping) {
        throw new NotFoundException('resource');
    }

    const extraInfo = await context.services.coreInternal.resource.structure(
        idMapping.id
    );

    return {
        edlibId: idMapping.id,
        name: extraInfo.name,
        createdAt: extraInfo.created,
    };
};

export default {
    getForResource: async (req, res, next) => {
        const idMapping = await req.context.services.id.getForId(
            req.params.resourceId
        );

        if (!idMapping) {
            throw new NotFoundException('resource');
        }

        const versionResource = await req.context.services.version.getForResource(
            idMapping.externalSystemName,
            idMapping.externalSystemId
        );

        const flatten = (resource, result = []) => {
            if (!resource) {
                return result;
            }

            if (resource.parent) {
                result = flatten(resource.parent, []);
            }

            result.unshift({ ...resource, parent: undefined });

            return result;
        };

        return await Promise.all(
            flatten(versionResource).map(async (resource) => {
                return await decorateResource(req.context, resource);
            })
        );
    },
};
