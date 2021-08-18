import { NotFoundException, ApiException } from '@cerpus/edlib-node-utils';

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
        throw new ApiException('Endpoint is not implemented');
    },
};
