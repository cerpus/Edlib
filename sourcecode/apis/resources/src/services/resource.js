import Joi from 'joi';
import {
    validateJoi,
    NotFoundException,
    ApiException,
    logger,
} from '@cerpus/edlib-node-utils';
import resourceCapabilities from '../constants/resourceCapabilities.js';
import moment from 'moment';
import apiConfig from '../config/apis.js';
import licenses from '../constants/licenses.js';
import { syncResource } from './elasticSearch.js';
import externalSystemService from './externalSystem.js';

const getElasticVersionFieldKey = (isListedFetch) =>
    isListedFetch ? 'publicVersion' : 'protectedVersion';
const orderByRegex = /^([a-zA-Z]*)(\((desc|asc)\))?$/;
const getResourcesFromRequestValidation = (data) => {
    return validateJoi(
        data,
        Joi.object({
            limit: Joi.number().optional().min(1).default(20),
            offset: Joi.number().optional().min(0).default(0),
            contentFilter: Joi.string().default('').optional(),
            orderBy: Joi.string()
                .regex(orderByRegex)
                .default('updatedAt(desc)')
                .optional(),
            searchString: Joi.string()
                .min(1)
                .optional()
                .empty('')
                .allow(null)
                .default(null),
            licenses: Joi.array().items(Joi.string()).default([]).optional(),
            contentTypes: Joi.array()
                .items(Joi.string())
                .default([])
                .optional(),
            languages: Joi.array().items(Joi.string()).default([]).optional(),
        })
    );
};

const _groupsManager = (groupsInfo) => {
    const groups = groupsInfo.reduce((groups, groupInfo) => {
        groups[groupInfo.name] = {
            name: groupInfo.name,
            fieldPath: groupInfo.fieldPath,
            ignoreCountQuery: groupInfo.ignoreCountQuery,
            boolMust: null,
            boolMustCount: [],
        };

        return groups;
    }, {});
    const globalFilters = [];
    const filtersToLog = [];

    return {
        addFilter: (groupName, boolMust) => {
            groups[groupName].boolMust = boolMust;
            Object.values(groups)
                .filter((group) => group.name !== groupName)
                .forEach((group) =>
                    groups[group.name].boolMustCount.push(boolMust)
                );
        },
        addFiltersToLog: (groupName, filters) => {
            filters.forEach((value) =>
                filtersToLog.push({
                    groupName,
                    value,
                })
            );
        },
        addGlobalFilter: (boolMust) => {
            globalFilters.push(boolMust);
        },
        getSearchQuery: () => {
            return {
                bool: {
                    must: [
                        ...globalFilters,
                        ...Object.values(groups)
                            .filter((group) => group.boolMust !== null)
                            .map((group) => group.boolMust),
                    ],
                },
            };
        },
        getCountQueryGroups: () =>
            Object.values(groups)
                .filter((group) => !group.ignoreCountQuery)
                .map((group) => {
                    const must = [...globalFilters, ...group.boolMustCount];
                    return {
                        name: group.name,
                        aggs: {
                            [group.name]: {
                                terms: { field: group.fieldPath, size: 10000 },
                            },
                        },
                        query:
                            must.length === 0
                                ? undefined
                                : {
                                      bool: {
                                          must,
                                      },
                                  },
                    };
                }),
        getFiltersToLog: () => filtersToLog,
    };
};

const parseOrderBy = (orderBy, prefix) => {
    const allowedFields = ['views', 'updatedAt', 'relevant'];
    const allowedDirections = ['asc', 'desc'];
    const result = {
        direction: 'desc',
        column: 'updatedAt',
    };
    const fieldMap = {
        relevant: '_score',
        updatedAt: `${prefix}.updatedAt`,
    };

    const match = orderBy.match(orderByRegex);
    const matchField = match[1];
    const matchDirection = match[3];

    if (matchDirection && allowedDirections.indexOf(matchDirection) !== -1) {
        result.direction = matchDirection;
    }

    if (allowedFields.indexOf(matchField) !== -1) {
        result.column = matchField;
    }

    if (fieldMap[matchField]) {
        result.column = fieldMap[matchField];
    }

    return result;
};

const getResourcesFromRequest = async (req, tenantId) => {
    // ensure licenses is always an array
    if (req.query.licenses && !Array.isArray(req.query.licenses)) {
        req.query.licenses = [req.query.licenses];
    }

    if (req.query.languages && !Array.isArray(req.query.languages)) {
        req.query.languages = [req.query.languages];
    }

    if (req.query.contentTypes && !Array.isArray(req.query.contentTypes)) {
        req.query.contentTypes = [req.query.contentTypes];
    }

    const {
        limit,
        offset,
        orderBy,
        licenses,
        searchString,
        contentTypes,
        languages,
        contentFilter,
    } = getResourcesFromRequestValidation(req.query);

    const field = !tenantId ? 'publicVersion' : 'protectedVersion';
    const groups = _groupsManager([
        {
            name: 'licenses',
            fieldPath: `${field}.license.keyword`,
        },
        {
            name: 'languages',
            fieldPath: `${field}.language.keyword`,
        },
        {
            name: 'search',
            fieldPath: `${field}.title`,
            ignoreCountQuery: true,
        },
        {
            name: 'contentTypes',
            fieldPath: `${field}.contentType.keyword`,
        },
    ]);

    if (licenses.length !== 0) {
        groups.addFilter('licenses', {
            bool: {
                should: licenses.map((license) => ({
                    match_phrase: {
                        [`${field}.license.keyword`]: license.toLowerCase(),
                    },
                })),
            },
        });
        groups.addFiltersToLog(
            'licenses',
            licenses.map((l) => l.toLowerCase())
        );
    }

    if (languages.length !== 0) {
        groups.addFilter('languages', {
            bool: {
                should: languages.map((language) => ({
                    match_phrase: {
                        [`${field}.language.keyword`]: language.toLowerCase(),
                    },
                })),
            },
        });
        groups.addFiltersToLog(
            'languages',
            languages.map((l) => l.toLowerCase())
        );
    }

    if (searchString) {
        const matches = searchString.match(/(\w{4,12}-?){5}/);

        groups.addFilter('search', {
            bool: {
                should: [
                    {
                        match: {
                            [`${field}.title`]: {
                                query: searchString,
                                fuzziness: 'AUTO',
                            },
                        },
                    },
                    matches !== null && {
                        match: {
                            [`id`]: {
                                query: matches[0],
                            },
                        },
                    },
                ].filter(Boolean),
            },
        });
    }

    if (contentTypes) {
        groups.addFilter('contentTypes', {
            bool: {
                should: contentTypes.map((contentType) => ({
                    match_phrase: {
                        [`${field}.contentType.keyword`]:
                            contentType.toLowerCase(),
                    },
                })),
            },
        });
        groups.addFiltersToLog(
            'contentTypes',
            contentTypes.map((ct) => ct.toLowerCase())
        );
    }

    groups.addGlobalFilter({
        exists: {
            field,
        },
    });

    if (tenantId) {
        groups.addGlobalFilter({
            match: {
                protectedUserIds: tenantId,
            },
        });
    }

    const parsedOrderBy = parseOrderBy(
        orderBy,
        getElasticVersionFieldKey(tenantId === null)
    );

    const { body } = await req.context.services.elasticsearch.client.search({
        index: apiConfig.elasticsearch.resourceIndexPrefix,
        track_total_hits: true,
        body: {
            from: offset,
            size: limit,
            query: groups.getSearchQuery(),
            sort: [
                {
                    [parsedOrderBy.column]: { order: parsedOrderBy.direction },
                },
            ],
        },
    });

    const response = await Promise.all(
        groups.getCountQueryGroups().map(async (group) => {
            return {
                name: group.name,
                aggregations:
                    await req.context.services.elasticsearch.client.search({
                        index: apiConfig.elasticsearch.resourceIndexPrefix,
                        body: {
                            aggs: group.aggs,
                            query: group.query,
                        },
                    }),
            };
        })
    );

    const resourceSearch = await req.context.db.resourceSearch.create({
        userId: tenantId,
        contentFilter,
        searchString,
        orderBy,
        offset,
        limit,
    });

    await Promise.all(
        groups.getFiltersToLog().map((ftl) =>
            req.context.db.resourceSearchFilter.create({
                resourceSearchId: resourceSearch.id,
                ...ftl,
            })
        )
    );

    return {
        pagination: {
            totalCount: body.hits.total.value,
            limit,
            offset,
        },
        data: await transformElasticResources(
            req.context,
            body.hits.hits,
            !tenantId
        ),
        filterCount: response.reduce((filterCount, response) => {
            filterCount[response.name] =
                response.aggregations.body.aggregations[
                    response.name
                ].buckets.map((bucket) => ({
                    key: bucket.key,
                    count: bucket.doc_count,
                }));
            return filterCount;
        }, {}),
    };
};

const transformElasticResources = async (
    context,
    elasticsearchResources,
    isPublicResources
) => {
    const resources = await context.db.resource.getByIds(
        elasticsearchResources.map((esr) => esr._source.id)
    );
    const resourceVersions = await context.db.resourceVersion.getByIds(
        elasticsearchResources.map(
            (esr) =>
                esr._source[getElasticVersionFieldKey(isPublicResources)].id
        )
    );

    function publicCapabilities(license = '') {
        const capabilities = [resourceCapabilities.VIEW];

        if (
            license !== licenses.EDLIB &&
            !license.includes(licenses.CC_ELEMENT_ND)
        ) {
            capabilities.push(resourceCapabilities.EDIT);
        }

        return capabilities;
    }

    let results = elasticsearchResources
        .map((esr) => {
            return {
                ...resources.find((r) => r.id === esr._source.id),
                version: resourceVersions.find(
                    (rv) =>
                        rv.id ===
                        esr._source[
                            getElasticVersionFieldKey(isPublicResources)
                        ].id
                ),
                resourceCapabilities: isPublicResources
                    ? publicCapabilities(
                          esr._source.publicVersion.license?.toLowerCase()
                      )
                    : [resourceCapabilities.VIEW, resourceCapabilities.EDIT],
                analytics: {
                    viewCount: esr._source.views,
                },
            };
        })
        .filter((esr) => esr.version);

    let contentTypeInfoToFetch = results.reduce(
        (contentTypeInfoToFetch, result) => {
            if (
                !contentTypeInfoToFetch.some(
                    ({ externalSystemName, contentType }) =>
                        result.version.externalSystemName ===
                            externalSystemName &&
                        result.version.contentType === contentType
                )
            ) {
                contentTypeInfoToFetch.push({
                    externalSystemName: result.version.externalSystemName,
                    contentType: result.version.contentType,
                });
            }
            return contentTypeInfoToFetch;
        },
        []
    );

    const contentTypeInfo = (
        await Promise.all(
            contentTypeInfoToFetch.map((ctitf) =>
                context.services.externalResourceFetcher.getContentTypeInfo(
                    ctitf.externalSystemName,
                    ctitf.contentType
                )
            )
        )
    ).filter(Boolean);

    return results.map((result) => ({
        ...result,
        contentTypeInfo: contentTypeInfo.find(
            (cti) =>
                cti.externalSystemName === result.version.externalSystemName &&
                cti.contentType === result.version.contentType
        ),
    }));
};

const retrieveCoreInfo = async (context, resourceVersions) => {
    try {
        const coreInfos =
            await context.services.coreInternal.resource.multipleFromExternalIdInfo(
                resourceVersions.map((rv) => ({
                    externalSystemName: rv.externalSystemName,
                    externalSystemId: rv.externalSystemId,
                }))
            );

        for (let {
            externalSystemName,
            externalSystemId,
            resourceInfo,
        } of coreInfos) {
            const resourceVersion = resourceVersions.find(
                (rv) =>
                    rv.externalSystemName === externalSystemName &&
                    rv.externalSystemId === externalSystemId
            );

            if (!resourceVersion) {
                throw new ApiException('Resource not found');
            }

            if (resourceInfo && resourceInfo.deletedAt) {
                await context.db.resource.update(resourceVersion.resourceId, {
                    deletedAt: moment(resourceInfo.deletedAt).toDate(),
                });
            }

            if (resourceInfo && resourceInfo.uuid) {
                await context.db.resourceVersion.update(resourceVersion.id, {
                    id: resourceInfo.uuid,
                });
            }
        }
    } catch (e) {
        if (!(e instanceof NotFoundException)) {
            throw e;
        }
    }
};

const status = async (context, resourceId) => {
    const resourceVersion =
        await context.db.resourceVersion.getLatestNonDraftResourceVersion(
            resourceId
        );

    const isPublished = resourceVersion && resourceVersion.isPublished;

    return {
        isListed: resourceVersion && isPublished && resourceVersion.isListed,
        isPublished,
    };
};

const isPublished = async (context, resourceId) => {
    const resourceStatus = await status(context, resourceId);

    return resourceStatus.isPublished;
};

export class ResourceVersionNotCreatedError extends Error {
    constructor() {
        super('Resource version was not created.');
    }
}

const saveResourceToDb = async (context, validatedData) => {
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
                'Version was not found for resource. Make sure versions are saved into the versionapi. It is required to build the resource data model'
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
            actualVersion
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

const validateResource = (data) => {
    return validateJoi(
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
}

export const saveResource = async (context, data, {
    saveToSearchIndex = true,
    waitForIndex = false,
}) => {
    const validatedData = validateResource(data);

    if (validatedData.license) {
        validatedData.license = validatedData.license.toLowerCase();
    }

    const resourceVersion = await saveResourceToDb(context, validatedData);

    if (!resourceVersion) {
        throw new ResourceVersionNotCreatedError();
    }

    if (saveToSearchIndex) {
        const resource = await context.db.resource
            .getById(resourceVersion.resourceId);

        await syncResource(context, resource, waitForIndex);
    }
};

export default {
    getResourcesFromRequest,
    transformElasticResources,
    retrieveCoreInfo,
    isPublished,
    status,
    saveResource,
    ResourceVersionNotCreatedError,
};
