export const syncResource = async (context, resource, waitForIndex) => {
    if (!resource) {
        return;
    }

    const shouldDelete = resource.deletedAt !== null;

    if (shouldDelete) {
        return context.services.elasticsearch.remove(resource.id);
    }

    const latestVersion = await context.db.resourceVersion.getLatestResourceVersion(
        resource.id
    );

    if (resource.deletedAt || !latestVersion) {
        return context.services.elasticsearch.remove(resource.id);
    }

    const latestPublishedVersion = await context.db.resourceVersion.getLatestPublishedResourceVersion(
        resource.id
    );

    const latestVersionCollaborators = await context.db.resourceVersionCollaborator.getWithTenantsForResourceVersion(
        latestVersion.id
    );

    const resourceVersionToElasticVersion = (resourceVersion) => ({
        id: resourceVersion.id,
        externalSystemName: resourceVersion.externalSystemName,
        title: resourceVersion.title,
        description: resourceVersion.description,
        license: resourceVersion.license,
        language: resourceVersion.language,
        contentType: resourceVersion.contentType,
        isListed: resourceVersion.isListed === 1,
        updatedAt: resourceVersion.updatedAt,
        createdAt: resourceVersion.createdAt,
    });

    console.log(resource.id);
    console.log(latestVersionCollaborators.map((c) => c.tenantId));

    const elasticData = {
        id: resource.id,
        publicVersion:
            latestPublishedVersion && latestPublishedVersion.isListed
                ? resourceVersionToElasticVersion(latestPublishedVersion)
                : undefined,
        protectedVersion: resourceVersionToElasticVersion(latestVersion),
        protectedUserIds: [
            latestVersion.ownerId,
            ...latestVersionCollaborators.map((c) => c.tenantId),
        ],
    };

    await context.services.elasticsearch.updateOrCreate(
        elasticData,
        waitForIndex
    );
};
