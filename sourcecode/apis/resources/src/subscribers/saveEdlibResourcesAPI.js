import { buildRawContext } from '../context/index.js';
import Joi from 'joi';
import { logger, validateJoi } from '@cerpus/edlib-node-utils';
import * as elasticSearchService from '../services/elasticSearch.js';
import externalSystemService from '../services/externalSystem.js';

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
    let versionPurpose = 'create';
    let actualVersion = null;

    if (
        externalSystemService.isVersioningEnabled(
            resourceVersionValidatedData.externalSystemName,
            resourceVersionValidatedData.contentType
        )
    ) {
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

        versionPurpose = version.versionPurpose.toLowerCase();
        actualVersion = version;
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

    let createdResourceGroup = null;
    let createdResource = null;

    // Purpose is update. a version with purpose update should always have a parent version.
    if (['update', 'upgrade'].indexOf(versionPurpose) !== -1) {
        const resource = await findResourceFromParentVersions(
            context,
            actualVersion
        );

        if (!resource) {
            return;
        }

        dbResourceVersionData.resourceId = resource.id;
    } else if (versionPurpose === 'translation') {
        const siblingResource = await findResourceFromParentVersions(
            context,
            actualVersion && actualVersion.parent
        );

        if (!siblingResource) {
            return;
        }

        createdResource = await context.db.resource.create({
            resourceGroupId: siblingResource.resourceGroupId,
        });

        dbResourceVersionData.resourceId = createdResource.id;
    } else if (
        ['create', 'copy', 'import', 'initial'].indexOf(versionPurpose) !== -1
    ) {
        createdResourceGroup = await context.db.resourceGroup.create({});
        createdResource = await context.db.resource.create({
            resourceGroupId: createdResourceGroup.id,
        });

        dbResourceVersionData.resourceId = createdResource.id;
    } else {
        console.error(`Unknown version purpose ${versionPurpose}`);
        return;
    }

    try {
        return await context.db.resourceVersion.create(dbResourceVersionData);
    } catch (e) {
        if (e.code === 'ER_DUP_ENTRY') {
            const resourceVersion = await context.db.resourceVersion.getByExternalId(
                resourceVersionValidatedData.externalSystemName,
                resourceVersionValidatedData.externalSystemId
            );

            if (resourceVersion) {
                if (createdResource) {
                    await context.db.resource.remove(createdResource.id);
                }

                if (createdResourceGroup) {
                    await context.db.resourceGroup.remove(
                        createdResourceGroup.id
                    );
                }

                return resourceVersion;
            }
        }

        throw e;
    }
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
                ownerId: Joi.string()
                    .min(1)
                    .allow(null)
                    .empty(null)
                    .optional()
                    .default(null),
                isPublished: Joi.boolean().required(),
                isListed: Joi.boolean().required(),
                language: Joi.string()
                    .min(1)
                    .allow(null)
                    .optional()
                    .default(null),
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
                    .optional()
                    .default([]),
                emailCollaborators: Joi.array()
                    .items(Joi.string().email())
                    .min(0)
                    .optional()
                    .default([]),
                authorOverwrite: Joi.string()
                    .min(1)
                    .optional()
                    .allow(null)
                    .empty(null)
                    .default(null),
            })
        );
    } catch (e) {
        // @todo log this somewhere
        console.error(e);
        return;
    }

    if (validatedData.license) {
        validatedData.license = validatedData.license.toLowerCase();
    }

    const context = buildRawContext({}, {}, { pubSubConnection });

    const resourceVersion = await saveToDb(context, validatedData);

    if (!resourceVersion) {
        console.error('Resource version was not created.');
        return;
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
