const isResourceVersionViewable = async (context, resourceVersion) => {
    return resourceVersion.isPublished && !resourceVersion.isDraft;
};

const isResourceVersionViewableByTenant = async (
    context,
    resourceVersion,
    tenantId
) => {
    if (await isResourceVersionViewable(context, resourceVersion)) {
        return true;
    }

    const resource = await context.db.resource.getById(
        resourceVersion.resourceId
    );

    if (!resource) {
        return false;
    }

    if (await hasResourceAccess(context, resource, tenantId)) {
        return true;
    }

    return false;
};

const hasResourceAccess = async (context, resource, tenantId) => {
    let resourceVersion = await context.db.resourceVersion.getLatestResourceVersion(
        resource.id
    );

    if (!resourceVersion) {
        return false;
    }

    if (resourceVersion.ownerId === tenantId) {
        return true;
    }

    const collaborators = await context.db.resourceVersionCollaborator.getForResourceVersion(
        resourceVersion.id
    );

    if (collaborators.some((c) => c.tenantId === tenantId)) {
        return true;
    }

    return false;
};

export default {
    isResourceVersionViewable,
    isResourceVersionViewableByTenant,
    hasResourceAccess,
};
