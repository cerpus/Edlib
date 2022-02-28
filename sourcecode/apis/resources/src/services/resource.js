import Joi from 'joi';
import _ from 'lodash';
import {
    validateJoi,
    NotFoundException,
    ApiException,
} from '@cerpus/edlib-node-utils';
import resourceCapabilities from '../constants/resourceCapabilities.js';
import moment from 'moment';
import apiConfig from '../config/apis.js';
import licenses from '../constants/licenses.js';

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
    const allowedFields = ['views', 'updatedAt'];
    const allowedDirections = ['asc', 'desc'];
    const fieldsToPrefix = ['updatedAt'];

    const result = {
        direction: 'desc',
        column: 'updatedAt',
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

    if (fieldsToPrefix.indexOf(result.column) !== -1) {
        result.column = `${prefix}.${result.column}`;
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
        groups.addFilter('search', {
            match: {
                [`${field}.title`]: {
                    query: searchString,
                    fuzziness: 'AUTO',
                },
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
        const capabilities = [
            resourceCapabilities.VIEW,
        ];

        if (license !== licenses.EDLIB && !license.includes(licenses.CC_ELEMENT_ND)) {
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
                resourceCapabilities: isPublicResources ?
                    publicCapabilities(esr._source.publicVersion.license?.toLowerCase()) :
                    [
                        resourceCapabilities.VIEW,
                        resourceCapabilities.EDIT,
                    ]
                ,
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

export default {
    getResourcesFromRequest,
    transformElasticResources,
    retrieveCoreInfo,
    isPublished,
    status,
};
