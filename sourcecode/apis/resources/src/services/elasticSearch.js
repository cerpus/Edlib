import resourceService from './resource.js';

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

    const resourceStatus = await resourceService.status(context, resource.id);

    let publicVersion;
    if (resourceStatus.isListed) {
        publicVersion = await context.db.resourceVersion.getLatestNonDraftResourceVersion(
            resource.id
        );
    }

    const latestVersionCollaborators = await context.db.resourceVersionCollaborator.getWithTenantsForResourceVersion(
        latestVersion.id
    );

    const viewCount = await context.db.trackingResourceVersion.getCountForResource(
        latestVersion.resourceId
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
        authorOverwrite: resourceVersion.authorOverwrite,
        updatedAt: resourceVersion.updatedAt,
        createdAt: resourceVersion.createdAt,
    });

    const elasticData = {
        id: resource.id,
        publicVersion: publicVersion
            ? resourceVersionToElasticVersion(publicVersion)
            : null,
        protectedVersion: resourceVersionToElasticVersion(latestVersion),
        protectedUserIds: [
            latestVersion.ownerId,
            ...latestVersionCollaborators.map((c) => c.tenantId),
        ],
        views: viewCount,
    };

    await context.services.elasticsearch.updateOrCreate(
        elasticData,
        waitForIndex
    );
};
