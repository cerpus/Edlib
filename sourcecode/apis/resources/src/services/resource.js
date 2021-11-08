import Joi from 'joi';
import _ from 'lodash';
import {
    validateJoi,
    NotFoundException,
    ApiException,
} from '@cerpus/edlib-node-utils';
import resourceCapabilities from '../constants/resourceCapabilities.js';
import moment from 'moment';

const getElasticVersionFieldKey = (isListedFetch) =>
    isListedFetch ? 'publicVersion' : 'protectedVersion';

const getResourcesFromRequestValidation = (data) => {
    return validateJoi(
        data,
        Joi.object({
            limit: Joi.number().optional().min(1).default(20),
            offset: Joi.number().optional().min(0).default(0),
            orderBy: Joi.string()
                .allow('created', 'usage')
                .default('created')
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
        })
    );
};

const getResourcesFromRequest = async (req, tenantId) => {
    // ensure licenses is always an array
    if (req.query.licenses && !Array.isArray(req.query.licenses)) {
        req.query.licenses = [req.query.licenses];
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
    } = getResourcesFromRequestValidation(req.query);

    const field = !tenantId ? 'publicVersion' : 'protectedVersion';

    let extraQuery = {};

    if (licenses.length !== 0) {
        _.set(extraQuery, 'bool.must', [
            ..._.get(extraQuery, 'bool.must', []),
            {
                bool: {
                    should: licenses.map((license) => ({
                        match_phrase: {
                            [`${field}.license.keyword`]: license.toLowerCase(),
                        },
                    })),
                },
            },
        ]);
    }

    if (searchString) {
        _.set(extraQuery, 'bool.must', [
            ..._.get(extraQuery, 'bool.must', []),
            {
                match: {
                    [`${field}.title`]: {
                        query: searchString,
                        fuzziness: 'AUTO',
                    },
                },
            },
        ]);
    }

    if (contentTypes) {
        _.set(extraQuery, 'bool.must', [
            ..._.get(extraQuery, 'bool.must', []),
            {
                bool: {
                    should: contentTypes.map((contentType) => ({
                        match_phrase: {
                            [`${field}.contentType.keyword`]: contentType.toLowerCase(),
                        },
                    })),
                },
            },
        ]);
    }

    const { body } = await req.context.services.elasticsearch.search(
        tenantId,
        {
            limit,
            offset,
        },
        {
            column:
                orderBy === 'usage'
                    ? 'views'
                    : `${getElasticVersionFieldKey(
                          tenantId === null
                      )}.createdAt`,
            direction: 'DESC',
        },
        Object.keys(extraQuery).length === 0 ? null : extraQuery
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
                resourceCapabilities: [
                    resourceCapabilities.VIEW,
                    resourceCapabilities.EDIT, //@todo fix based on type
                ],
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
        const coreInfos = await context.services.coreInternal.resource.multipleFromExternalIdInfo(
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
    const resourceVersion = await context.db.resourceVersion.getLatestNonDraftResourceVersion(
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
