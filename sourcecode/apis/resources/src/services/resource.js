import Joi from 'joi';
import _ from 'lodash';
import { validateJoi } from '@cerpus/edlib-node-utils/services/index.js';
import resourceCapabilities from '../constants/resourceCapabilities.js';

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
            column: `${getElasticVersionFieldKey(tenantId === null)}.${
                orderBy === 'usage' ? 'createdAt' : 'createdAt' //@todo use correct column
            }`,
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

    return elasticsearchResources.map((esr) => {
        return {
            ...resources.find((r) => r.id === esr._source.id),
            version: resourceVersions.find(
                (rv) =>
                    rv.id ===
                    esr._source[getElasticVersionFieldKey(isPublicResources)].id
            ),
            resourceCapabilities: [
                resourceCapabilities.VIEW,
                resourceCapabilities.EDIT, //@todo fix based on type
            ],
        };
    });
};

const hasResourceWriteAccess = async (context, resource, tenantId) => {
    const resourceVersion = await context.db.resourceVersion.getLatestPublishedResourceVersion(
        resource.id
    );

    if (!resourceVersion) {
        return false;
    }

    if (resourceVersion.ownerId !== tenantId) {
        return false;
    }

    return true;
};

export default {
    getResourcesFromRequest,
    transformElasticResources,
    hasResourceWriteAccess,
};
