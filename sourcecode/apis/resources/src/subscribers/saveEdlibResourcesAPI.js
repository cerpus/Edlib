import { buildRawContext } from '../context/index.js';
import { validateJoi } from '@cerpus/edlib-node-utils/services/index.js';
import Joi from 'joi';
import { logger } from '@cerpus/edlib-node-utils/index.js';
import * as elasticSearchService from '../services/elasticSearch.js';
import moment from 'moment';

const findResourceFromParentVersions = async (context, version) => {
    if (!version) {
        logger.error('Version was not found for resource');
        return;
    }

    const versionParents = await context.services.version.getVersionParents(
        version.id
    );

    if (!versionParents || versionParents.length === 0) {
        logger.error(
            `Unexpected response from version API. A version with purpose "update" must always have parents`
        );
        return;
    }

    const resourceVersion = await context.db.resourceVersion.getFirstFromExternalSytemReference(
        versionParents.map((vp) => ({
            externalSystemName: vp.externalSystem,
            externalSystemId: vp.externalReference,
        }))
    );

    if (!resourceVersion) {
        logger.error(
            `History is not in sync and we can therefore not update the data model.`
        );
        return;
    }

    return context.db.resource.getById(resourceVersion.resourceId);
};

const saveResourceVersion = async (context, resourceVersionValidatedData) => {
    const version = await context.services.version.getForResource(
        resourceVersionValidatedData.externalSystemName,
        resourceVersionValidatedData.externalSystemId
    );

    if (!version) {
        logger.error(
            'Version was not found for resource. Make sure versions are saved into the versioningapi. It is required to build the resource data model'
        );
        return;
    }

    const dbResourceVersion = await context.db.resourceVersion.getByExternalId(
        resourceVersionValidatedData.externalSystemName,
        resourceVersionValidatedData.externalSystemId
    );

    let dbResourceVersionData = {
        ...resourceVersionValidatedData,
    };

    if (dbResourceVersion) {
        return await context.db.resourceVersion.update(
            dbResourceVersion.id,
            dbResourceVersionData
        );
    }

    // Purpose is update. a version with purpose update should always have a parent version.
    if (version.versionPurpose.toLowerCase() === 'update') {
        const resource = await findResourceFromParentVersions(context, version);

        if (!resource) {
            return;
        }

        dbResourceVersionData.resourceId = resource.id;
    } else if (version.versionPurpose.toLowerCase() === 'translation') {
        const siblingResource = await findResourceFromParentVersions(
            context,
            version.parent
        );

        if (!siblingResource) {
            return;
        }

        const resource = await context.db.resource.create({
            resourceGroupId: siblingResource.resourceGroupId,
        });

        dbResourceVersionData.resourceId = resource.id;
    } else if (
        ['create', 'copy'].indexOf(version.versionPurpose.toLowerCase()) !== -1
    ) {
        const resourceGroup = await context.db.resourceGroup.create({});
        const resource = await context.db.resource.create({
            resourceGroupId: resourceGroup.id,
        });

        dbResourceVersionData.resourceId = resource.id;
    } else {
        console.error(`Unknown version purpose ${version.versionPurpose}`);
        return;
    }

    return await context.db.resourceVersion.create(dbResourceVersionData);
};

const saveToDb = async (context, validatedData) => {
    const {
        collaborators,
        emailCollaborators,
        ...resourceVersionValidatedData
    } = validatedData;

    const resourceVersion = await saveResourceVersion(
        context,
        resourceVersionValidatedData
    );

    if (!resourceVersion) {
        return;
    }

    let collaboratorIdMap = collaborators.reduce(
        (collaboratorIdMap, collaboratorId) => ({
            ...collaboratorIdMap,
            [collaboratorId]: {
                tenantId: collaboratorId,
            },
        }),
        {}
    );

    const usersFromEmail =
        emailCollaborators.length === 0
            ? []
            : await context.services.edlibAuth.getUsersByEmail(
                  emailCollaborators
              );

    collaboratorIdMap = usersFromEmail.reduce(
        (collaboratorIdMap, user) => ({
            ...collaboratorIdMap,
            [user.id]: {
                tenantId: user.id,
                email: user.email,
            },
        }),
        collaboratorIdMap
    );

    const emailWithoutUsers = emailCollaborators.filter(
        (emailCollaborator) =>
            !usersFromEmail.some((user) => user.email === emailCollaborator)
    );

    const collaboratorsData = [
        ...Object.values(collaboratorIdMap),
        ...emailWithoutUsers.map((email) => ({ email })),
    ];

    const resourceVersionCollaborators = await context.db.resourceVersionCollaborator.getForResourceVersion(
        resourceVersion.id
    );

    const toDelete = resourceVersionCollaborators.filter(
        (resourceVersionCollaborator) => {
            if (resourceVersionCollaborator.tenantId) {
                return !collaboratorIdMap[resourceVersionCollaborator.tenantId];
            }

            return !emailWithoutUsers.some(
                (email) => email === resourceVersionCollaborator.email
            );
        }
    );

    const getDbRowFromCollaboratorData = (collaboratorData) => {
        if (collaboratorData.tenantId) {
            return resourceVersionCollaborators.find(
                (resourceVersionCollaborator) =>
                    resourceVersionCollaborator.tenantId ===
                    collaboratorData.tenantId
            );
        }

        return resourceVersionCollaborators.find(
            (resourceVersionCollaborator) =>
                resourceVersionCollaborator.email === collaboratorData.email
        );
    };

    const toCreate = collaboratorsData.filter(
        (collaboratorData) => !getDbRowFromCollaboratorData(collaboratorData)
    );

    const toUpdate = collaboratorsData.reduce((toUpdate, collaboratorData) => {
        const dbRow = getDbRowFromCollaboratorData(collaboratorData);

        if (!dbRow) {
            return toUpdate;
        }

        return [
            ...toUpdate,
            {
                ...collaboratorData,
                id: dbRow.id,
            },
        ];
    }, []);

    await context.db.resourceVersionCollaborator.remove(
        toDelete.map((collaboratorToDelete) => collaboratorToDelete.id)
    );

    await Promise.all(
        toCreate.map((collaboratorToCreate) =>
            context.db.resourceVersionCollaborator.create({
                ...collaboratorToCreate,
                resourceVersionId: resourceVersion.id,
            })
        )
    );

    await Promise.all(
        toUpdate.map((collaboratorToUpdate) =>
            context.db.resourceVersionCollaborator.update(
                collaboratorToUpdate.id,
                collaboratorToUpdate
            )
        )
    );

    return resourceVersion;
};

export default ({ pubSubConnection }) => async (
    data,
    saveToSearchIndex = true,
    waitForIndex
) => {
    let validatedData;
    try {
        validatedData = validateJoi(
            data,
            Joi.object({
                externalSystemName: Joi.string().min(1).required(),
                externalSystemId: Joi.string().min(1).required(),
                title: Joi.string().min(1).required(),
                ownerId: Joi.string().min(1).required(),
                isPublished: Joi.boolean().required(),
                isListed: Joi.boolean().required(),
                language: Joi.string().min(1).required(),
                contentType: Joi.string().min(1).optional(),
                license: Joi.string().allow(null).optional().default(null),
                maxScore: Joi.number()
                    .min(1)
                    .allow(null)
                    .empty(0)
                    .optional()
                    .default(null),
                updatedAt: Joi.date().iso().required(),
                createdAt: Joi.date().iso().required(),
                collaborators: Joi.array()
                    .items(Joi.string().min(1))
                    .optional(),
                emailCollaborators: Joi.array()
                    .items(Joi.string().email())
                    .min(0)
                    .optional()
                    .default([]),
            })
        );
    } catch (e) {
        // @todo log this somewhere
        console.error(e);
        return;
    }
    const context = buildRawContext({}, {}, { pubSubConnection });

    const resourceVersion = await saveToDb(context, validatedData);

    if (!resourceVersion) {
        console.error('Resource version was not created.');
        return;
    }

    try {
        const info = await context.services.coreInternal.resource.fromExternalIdInfo(
            resourceVersion.externalSystemName,
            resourceVersion.externalSystemId
        );

        if (info.deletedAt) {
            await context.db.resource.update(resourceVersion.resourceId, {
                deletedAt: moment(info.deletedAt).toDate(),
            });
        }

        if (info && info.uuid) {
            await context.db.resourceVersion.update(resourceVersion.id, {
                id: info.uuid,
            });
        }
    } catch (e) {
        console.error(e);
    }

    if (saveToSearchIndex) {
        const resource = await context.db.resource.getById(
            resourceVersion.resourceId
        );

        await elasticSearchService.syncResource(
            context,
            resource,
            waitForIndex
        );
    }
};
