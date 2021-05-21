import { buildRawContext } from '../context/index.js';
import { validateJoi } from '@cerpus/edlib-node-utils/services/index.js';
import Joi from 'joi';
import { logger } from '@cerpus/edlib-node-utils/index.js';
import * as elasticSearchService from '../services/elasticSearch.js';

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

const saveToDb = async (context, validatedData) => {
    const version = await context.services.version.getForResource(
        validatedData.externalSystemName,
        validatedData.externalSystemId
    );

    if (!version) {
        logger.error(
            'Version was not found for resource. Make sure versions are saved into the versioningapi. It is required to build the resource data model'
        );
        return;
    }

    const dbResourceVersion = await context.db.resourceVersion.getByExternalId(
        validatedData.externalSystemName,
        validatedData.externalSystemId
    );

    let dbResourceVersionData = {
        ...validatedData,
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
                updatedAt: Joi.date().iso().required(),
                createdAt: Joi.date().iso().required(),
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

        if (!resource) {
            return;
        }

        await elasticSearchService.syncResource(
            context,
            resource,
            waitForIndex
        );
    }
};
